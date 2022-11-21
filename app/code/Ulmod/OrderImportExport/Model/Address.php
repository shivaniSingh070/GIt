<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model;

use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\DataObject;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Directory\Model\Config\Source\Country as CountryList;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
        
class Address
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Mapper
     */
    private $addressMapper;

    /**
     * @var CountryList
     */
    private $countryList;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var CollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param Mapper $addressMapper
     * @param CountryList $country
     * @param RegionInterfaceFactory $regionFactory
     * @param CollectionFactory $regionCollectionFactory
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        Mapper $addressMapper,
        CountryList $country,
        RegionInterfaceFactory $regionFactory,
        CollectionFactory $regionCollectionFactory
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressMapper = $addressMapper;
        $this->countryList = $country->toOptionArray(false);
        $this->regionFactory = $regionFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @param AddressInterface|DataObject|array|string $address
     * @param string $countryId
     * @return bool|RegionInterface
     */
    public function getRegion($address, $countryId)
    {
        if ($address instanceof \Magento\Customer\Api\Data\AddressInterface) {
            return $address->getRegion();
        } elseif (is_string($address) && $address) {
            /** @var \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection */
            $regionCollection = $this->regionCollectionFactory->create();
            
            $regionCollection->addCountryFilter($countryId);
            
            $regionCollection->addFieldToFilter(
                [
                    'main_table.code',
                    'rname.name',
                    'main_table.region_id'
                ],
                [$address, $address, $address]
            );

            /** @var RegionInterface $region */
            $region = $this->regionFactory->create();

            $itemsOptions = $regionCollection->getItems();
            if ($itemsOptions) {
                /** @var \Magento\Directory\Model\Region $regionItem */
                $regionItem = $regionCollection->getFirstItem();
                $region->setRegionCode($regionItem->getCode());
                $region->setRegion($regionItem->getName());
                $region->setRegionId($regionItem->getId());
            } else {
                $region->setRegion($address);
                $region->setRegionCode($address);
            }

            return $region;
        }

        return false;
    }

    /**
     * @param \Magento\Customer\Api\Data\AddressInterface|
     * DataObject|array|string $address
     * @return string|bool
     */
    public function getCountryId($address)
    {
        if ($address instanceof \Magento\Customer\Api\Data\AddressInterface) {
            return $address->getCountryId();
        }

        $needle = null;
        if (is_string($address)) {
            $needle = strtolower(trim($address));
        } elseif (is_array($address)) {
            $needle = strtolower(
                $address[AddressInterface::COUNTRY_ID]
            );
        }

        if ($needle !== null && $needle !== '') {
            foreach ($this->countryList as $countryOption) {
                if ($countryOption['value'] !== '') {
                    $haystack = [
                        strtolower($countryOption['value']),
                        strtolower($countryOption['label'])
                    ];
                    if (in_array($needle, $haystack)) {
                        return $countryOption['value'];
                    }
                }
            }
        }

        return false;
    }
    
    /**
     * @param AddressInterface|DataObject|array $address1
     * @param AddressInterface|DataObject|array $address2
     * @return bool
     */
    public function compareAddresses($address1, $address2)
    {
        $string1 = $this->getAddressComparisonString($address1);
        $string2 = $this->getAddressComparisonString($address2);

        return strcasecmp($string1, $string2) === 0;
    }

    /**
     * @param CustomerInterface $customer
     * @param \Magento\Customer\Api\Data\AddressInterface  $address
     * @return bool|int|null
     */
    public function getAddressId(
        CustomerInterface $customer,
        \Magento\Customer\Api\Data\AddressInterface $address
    ) {
        foreach ($customer->getAddresses() as $matchAddress) {
            if ($this->compareAddresses($address, $matchAddress)) {
                return $matchAddress->getId();
            }
        }

        return false;
    }

    /**
     * @param AddressInterface|DataObject|array $address
     * @return string
     */
    public function getAddressComparisonString($address)
    {
        $addressData = [];
        if ($address instanceof CustomerAddress) {
            $countryId = $this->getCountryId($address);
            
            $region = $this->getRegion(
                $address,
                $countryId
            );

            $addressData = [
                CustomerAddress::COMPANY => $address->getCompany(),
                CustomerAddress::PREFIX => $address->getPrefix(),
                CustomerAddress::FIRSTNAME => $address->getFirstname(),
                CustomerAddress::MIDDLENAME => $address->getMiddlename(),
                CustomerAddress::LASTNAME => $address->getLastname(),
                CustomerAddress::SUFFIX => $address->getSuffix(),
                CustomerAddress::STREET => $address->getStreet(),
                CustomerAddress::POSTCODE => $address->getPostcode(),
                CustomerAddress::REGION => $region,
                CustomerAddress::CITY => $address->getCity(),
                CustomerAddress::COUNTRY_ID => $countryId,
                CustomerAddress::FAX => $address->getFax(),
                CustomerAddress::TELEPHONE => $address->getTelephone(),
                CustomerAddress::VAT_ID => $address->getVatId()
            ];
        } elseif ($address instanceof DataObject) {
            $addressData = $address->getData();
        } elseif (is_array($address)) {
            $addressData = $address;
        }
        
        $addressData = array_map(
            function ($value) {
                if (is_string($value)) {
                    return $value;
                } elseif (is_array($value)) {
                    return implode('', $value);
                } elseif ($value instanceof RegionInterface) {
                    return $value->getRegion();
                }
            },
            $addressData
        );

        return preg_replace(
            '/[^\da-z]/i',
            '',
            strtolower(implode(
                '',
                array_filter($addressData, 'strlen')
            ))
        );
    }
}
