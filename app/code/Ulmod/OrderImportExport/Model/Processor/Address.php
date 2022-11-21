<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Ulmod\OrderImportExport\Model\Cache as ModelCache;
use Ulmod\OrderImportExport\Model\Address as ModelAddress;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Framework\Exception\LocalizedException;

class Address extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var ModelCache
     */
    private $cache;

    /**
     * @var AddressInterfaceFactory
     */
    private $objectFactory;

    /**
     * @var ModelAddress
     */
    private $modelAddress;

    /**
     * @var AddressRepositoryInterface
     */
    private $repository;

    /**
     * @var OrderAddressInterfaceFactory
     */
    private $orderAddressFactory;

    /**
     * @var string
     */
    private $addressType;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param ModelCache $cache
     * @param ModelAddress $modelAddress
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressRepositoryInterface $repository
     * @param CollectionFactory $collectionFactory
     * @param OrderAddressInterfaceFactory $orderAddressFactory
     * @param string $addressType
     * @param array $excludedFields
     */
    public function __construct(
        ModelCache $cache,
        ModelAddress $modelAddress,
        AddressInterfaceFactory $addressFactory,
        AddressRepositoryInterface $repository,
        CollectionFactory $collectionFactory,
        OrderAddressInterfaceFactory $orderAddressFactory,
        $addressType,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->cache = $cache;
        $this->modelAddress = $modelAddress;
        $this->objectFactory = $addressFactory;
        $this->repository  = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->orderAddressFactory = $orderAddressFactory;
        $this->addressType = strtolower($addressType);
    }

    /**
     * Address process
     *
     * @param array $data
     * @param OrderInterface $order
     * @return $this|mixed
     * @throws LocalizedException
     */
    public function process(array $data, OrderInterface $order)
    {
        $customerId = $order->getCustomerId();
        $this->cacheAddresses($customerId);

        $address = $this->toOrderAddress(
            $data,
            $this->getAddress($data, $order)
        );
        $address->setEmail($order->getCustomerEmail());
        $this->removeExcludedFields($address);

        $function = 'set' . ucfirst($this->addressType) . 'Address';
        $order->{$function}($address);

        return $this;
    }

    /**
     * @param array $data
     * @param AddressInterface $address
     * @return OrderAddressInterface
     */
    private function toOrderAddress(array $data, AddressInterface $address)
    {
        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $orderAddress */
        $orderAddress = $this->orderAddressFactory->create();
        
        $orderAddress->setCustomerId($address->getCustomerId());
        $orderAddress->setCustomerAddressId($address->getId());
        $orderAddress->setCustomerAddressData($address);
        $orderAddress->setCompany($address->getCompany());
        $orderAddress->setPrefix($address->getPrefix());
        $orderAddress->setLastname($address->getLastname());
        $orderAddress->setMiddlename($address->getMiddlename());
        $orderAddress->setFirstname($address->getFirstname());
        $orderAddress->setSuffix($address->getSuffix());
        $orderAddress->setStreet($address->getStreet());
        $orderAddress->setCity($address->getCity());

        $region = $address->getRegion();
        if ($region !== null) {
            $orderAddress->setRegion($region->getRegion());
            $orderAddress->setRegionCode($region->getRegionCode());
            $orderAddress->setRegionId($region->getRegionId());
        }

        $countryId = $address->getCountryId();
        $telephone = $address->getTelephone();
        $postcode = $address->getPostcode();
        $orderAddress->setPostcode($postcode);
        $orderAddress->setCountryId($countryId);
        $orderAddress->setTelephone($telephone);
        $orderAddress->setFax($address->getFax());
        $orderAddress->setVatId($address->getVatId());

        $key = $this->getPrefixedKey(OrderAddressInterface::VAT_IS_VALID);
        if (isset($data[$key])) {
            $orderAddress->setVatIsValid($data[$key]);
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::VAT_REQUEST_ID);
        if (isset($data[$key])) {
            $orderAddress->setVatRequestId($data[$key]);
        }
    
        $key = $this->getPrefixedKey(OrderAddressInterface::VAT_REQUEST_DATE);
        if (isset($data[$key])) {
            $orderAddress->setVatRequestDate($data[$key]);
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::VAT_REQUEST_SUCCESS);
        if (isset($data[$key])) {
            $orderAddress->setVatRequestSuccess($data[$key]);
        }

        return $orderAddress;
    }

    /**
     * Get address from cache.
     *
     * @param array $data
     * @param OrderInterface|\Magento\Sales\Model\Order $order
     * @return bool|AddressInterface
     * @throws LocalizedException
     */
    private function getAddress(array $data, OrderInterface $order)
    {
        $customerId = $order->getCustomerId();

        /** @var AddressInterface $address */
        $address = $this->objectFactory->create();
        
        $address->setCustomerId($customerId);

        $key = 'default_' . $this->addressType;
        if (isset($data[$key])) {
            $function = 'setIsDefault' . ucfirst($this->addressType);
            $address->{$function}((bool)$data[$key]);
        }
        
        $key = $this->getPrefixedKey(OrderAddressInterface::LASTNAME);
        if (isset($data[$key]) && !empty($data[$key])) {
            $address->setLastname($data[$key]);
        } else {
            $address->setLastname('N/A');
        }
        
        $key = $this->getPrefixedKey(OrderAddressInterface::MIDDLENAME);
        if (isset($data[$key])) {
            $address->setMiddlename($data[$key]);
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::FIRSTNAME);
        if (isset($data[$key]) && !empty($data[$key])) {
            $address->setFirstname($data[$key]);
        } else {
            $address->setFirstname('N/A');
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::PREFIX);
        if (isset($data[$key])) {
            $address->setPrefix($data[$key]);
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::SUFFIX);
        if (isset($data[$key])) {
            $address->setSuffix($data[$key]);
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::COMPANY);
        if (isset($data[$key])) {
            $address->setCompany($data[$key]);
        }

        $street = [];

        $streetAddressKey = $this->getPrefixedKey(
            OrderAddressInterface::STREET
        );
        $streetAddressFullKey = $this->getPrefixedKey('street_full');
        if (isset($data[$streetAddressFullKey])) {
            $street[] = $data[$streetAddressFullKey];
        } elseif (isset($data[$streetAddressKey])) {
            if (is_array($data[$streetAddressKey])) {
                $street = $data[$streetAddressKey];
            } else {
                $street[] = $data[$streetAddressKey];
            }
        } else {
            for ($i = 1; $i <= 8; $i++) {
                $key = $this->getPrefixedKey(
                    OrderAddressInterface::STREET . $i
                );
                if (isset($data[$key])) {
                    $street[] = $data[$key];
                }
            }
        }
        $address->setStreet($street);

        $key = $this->getPrefixedKey('country');
        if (isset($data[$key])) {
            $address->setCountryId(
                $this->modelAddress->getCountryId($data[$key])
            );
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::COUNTRY_ID);
        if (isset($data[$key])) {
            $address->setCountryId(
                $this->modelAddress->getCountryId($data[$key])
            );
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::CITY);
        if (isset($data[$key])) {
            $address->setCity($data[$key]);
        }
        
        $key = $this->getPrefixedKey('region');
        if (isset($data[$key])) {
            $region =  $this->modelAddress->getRegion($data[$key], $address->getCountryId());
            if ($region instanceof RegionInterface) {
                $address->setRegion($region);
            }
        }
    
        $key = $this->getPrefixedKey(OrderAddressInterface::POSTCODE);
        if (isset($data[$key])) {
            $address->setPostcode($data[$key]);
        }
        
        $key = $this->getPrefixedKey(OrderAddressInterface::REGION_ID);
        if (isset($data[$key])) {
            $region = $this->modelAddress->getRegion($data[$key], $address->getCountryId());
            if ($region instanceof RegionInterface) {
                $address->setRegion($region);
            }
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::TELEPHONE);
        if (isset($data[$key]) && strlen($data[$key])) {
            $address->setTelephone($data[$key]);
        } else {
            $address->setTelephone('1-800-403-8838');
        }

        $key = $this->getPrefixedKey(OrderAddressInterface::VAT_ID);
        if (isset($data[$key])) {
            $address->setVatId($data[$key]);
        }
    
        $key = $this->getPrefixedKey(OrderAddressInterface::FAX);
        if (isset($data[$key])) {
            $address->setFax($data[$key]);
        }

        if (!$order->getCustomerIsGuest() && $customerId) {
            $key = $this->modelAddress->getAddressComparisonString($address);
            $cachedAddress = $this->cache->getAddress($customerId, $key);
            if ($cachedAddress !== false) {
                return $cachedAddress;
            }

            $this->repository->save($address);
            $this->cacheAddress($address);
        }

        return $address;
    }

    /**
     * @param string $key
     * @return string
     */
    private function getPrefixedKey($key)
    {
        return trim($this->addressType) . '_' . trim($key);
    }

    /**
     * @param AddressInterface $address
     * @return $this
     */
    private function cacheAddress(AddressInterface $address)
    {
        $customerId = $address->getCustomerId();
        $this->cache->addAddress(
            $customerId,
            $address->getId(),
            $address
        );
        
        $this->cache->addAddress(
            $customerId,
            $this->modelAddress->getAddressComparisonString($address),
            $address
        );

        return $this;
    }

    /**
     * @param int $customerId
     * @return $this
     */
    private function cacheAddresses($customerId)
    {
        if ($customerId && !$this->cache->hasAddresses($customerId)) {
            /** @var \Magento\Customer\Model\ResourceModel\Address\Collection $collection */
            $collection = $this->collectionFactory->create();
            
            $collection->setCustomerFilter([$customerId]);

            /** @var \Magento\Customer\Model\Address $address */
            foreach ($collection as $address) {
                $this->cacheAddress(
                    $address->getDataModel()
                );
            }
        }

        return $this;
    }
}
