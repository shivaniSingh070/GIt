<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Ulmod\OrderImportExport\Model\Cache as ModelCache;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Customer\Model\Customer as CustomerModel;
    
class Customer extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var ModelCache
     */
    private $cache;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;
    
    /**
     * @var CustomerRepositoryInterface
     */
    private $repository;

    /**
     * @var CustomerInterfaceFactory
     */
    private $objectFactory;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param ModelCache $cache
     * @param CustomerInterfaceFactory $objectFactory
     * @param CustomerRepositoryInterface $repository
     * @param StoreRepositoryInterface $storeRepository
     * @param CollectionFactory $groupCollectionFactory
     * @param array $excludedFields
     */
    public function __construct(
        ModelCache $cache,
        CustomerInterfaceFactory $objectFactory,
        CustomerRepositoryInterface $repository,
        StoreRepositoryInterface $storeRepository,
        CollectionFactory $groupCollectionFactory,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->cache  = $cache;
        $this->objectFactory  = $objectFactory;
        $this->repository  = $repository;
        $this->storeRepository = $storeRepository;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * Customer process
     *
     * @param array $data
     * @param OrderInterface|OrderModel $order
     * @return $this|mixed
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function process(array $data, OrderInterface $order)
    {
        if (isset($data[OrderInterface::CUSTOMER_IS_GUEST])) {
            $order->setCustomerIsGuest(
                $data[OrderInterface::CUSTOMER_IS_GUEST]
            );
        }

        $billingData = !isset($data[OrderInterface::CUSTOMER_FIRSTNAME]) &&
                          !isset($data[OrderInterface::CUSTOMER_LASTNAME]);

        if ($billingData) {
            if (isset($data['billing_prefix'])) {
                $data[OrderInterface::CUSTOMER_PREFIX] = $data['billing_prefix'];
            }

            if (isset($data['billing_lastname'])) {
                $data[OrderInterface::CUSTOMER_LASTNAME] = $data['billing_lastname'];
            }
            
            if (isset($data['billing_middlename'])) {
                $data[OrderInterface::CUSTOMER_MIDDLENAME] = $data['billing_middlename'];
            }
            
            if (isset($data['billing_firstname'])) {
                $data[OrderInterface::CUSTOMER_FIRSTNAME] = $data['billing_firstname'];
            }

            if (isset($data['billing_suffix'])) {
                $data[OrderInterface::CUSTOMER_SUFFIX] = $data['billing_suffix'];
            }
        }

        if (!isset($data[OrderInterface::CUSTOMER_EMAIL])
                && isset($data['billing_email'])
            ) {
            $data[OrderInterface::CUSTOMER_EMAIL] = $data['billing_email'];
        }

        $customer = $this->getCustomer($data, $order);
        $order->setCustomer($customer);
        $order->setCustomerId($customer->getId());
        $order->setCustomerEmail($customer->getEmail());
        $order->setCustomerDob($customer->getDob());
        $order->setCustomerPrefix($customer->getPrefix());
        $order->setCustomerLastname($customer->getLastname());
        $order->setCustomerMiddlename($customer->getMiddlename());
        $order->setCustomerFirstname($customer->getFirstname());
        $order->setCustomerSuffix($customer->getSuffix());
        $order->setCustomerGroupId($customer->getGroupId());
        $order->setCustomerGender($customer->getGender());
        $order->setCustomerTaxvat($customer->getTaxvat());

        return $this;
    }

    /**
     * Get customer by id
     *
     * @param int $id
     * @return bool|CustomerInterface|CustomerData
     */
    private function getById($id)
    {
        try {
            $object = $this->cache->getCustomer($id);
            if ($id && $object === false) {
                $object = $this->repository
                    ->getById($id);
                    
                $email = $object->getEmail();
                $this->cache->addCustomer(
                    $id,
                    $object
                );
                $this->cache->addCustomer(
                    $email,
                    $object
                );
            }
        } catch (\Exception $e) {
            $object = false;
        }

        return $object;
    }

    /**
     * Get customer group id
     *
     * @param string|int $value
     * @return bool|int
     */
    public function getGroupId($value)
    {
        $this->cacheCustomerGroups();
        $customerGroup = $this->cache
            ->getCustomerGroup($value);
        if ($customerGroup !== false) {
            return $customerGroup->getId();
        }

        return false;
    }

    /**
     * Get customer by email
     *
     * @param string $email
     * @return bool|CustomerInterface|CustomerData
     */
    private function getByEmail($email)
    {
        try {
            $object = $this->cache->getCustomer($email);
            if ($email && $object === false) {
                $object = $this->repository
                    ->get($email);
                    
                $emailId = $object->getId();
                $this->cache->addCustomer(
                    $email,
                    $object
                );
                $this->cache->addCustomer(
                    $emailId,
                    $object
                );
            }
        } catch (\Exception $e) {
            $object = false;
        }

        return $object;
    }
    
    /**
     * Check if customer group cached
     *
     * @return $this
     */
    private function cacheCustomerGroups()
    {
        if (!$this->cache->hasCustomerGroups()) {
            /** @var $collection \Magento\Customer\Model\ResourceModel\Group\Collection  */
            $collection = $this->groupCollectionFactory->create();
            
            foreach ($collection as $group) {
                $this->cache->addCustomerGroup(
                    $group->getId(),
                    $group
                );
                $this->cache->addCustomerGroup(
                    $group->getCode(),
                    $group
                );
            }
        }

        return $this;
    }

    /**
     * Get customer
     *
     * @param array $data
     * @param OrderInterface $order
     * @return bool|CustomerInterface|CustomerModel|CustomerData
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function getCustomer(array $data, OrderInterface $order)
    {
        $customer = false;

        if (isset($data[OrderInterface::CUSTOMER_EMAIL])
            && !$order->getCustomerIsGuest()
        ) {
            $customer = $this->getByEmail(
                $data[OrderInterface::CUSTOMER_EMAIL]
            );
        }

        if ($customer === false && !$order->getCustomerIsGuest()) {
            if (isset($data[OrderInterface::CUSTOMER_ID])) {
                $customer = $this->getById(
                    $data[OrderInterface::CUSTOMER_ID]
                );
            }
        }

        // if customer can't be found by email or id, then create new one
        if ($customer === false) {
            /** @var CustomerInterface|CustomerModel $customer */
            $customer = $this->objectFactory->create();

            if (isset($data[OrderInterface::CUSTOMER_GROUP_ID])) {
                $groupId = $this->getGroupId(
                    $data[OrderInterface::CUSTOMER_GROUP_ID]
                );
                if ($groupId === false) {
                    $groupId = $order->getStore()->getWebsite()
                        ->getDefaultGroupId();
                }
            } else {
                $groupId = $order->getStore()->getWebsite()
                    ->getDefaultGroupId();
            }
            $customer->setGroupId($groupId);

            if (isset($data[OrderInterface::CUSTOMER_GENDER])) {
                $gender = $this->getGender(
                    $data[OrderInterface::CUSTOMER_GENDER]
                );
                if ($gender !== false) {
                    $customer->setGender($gender);
                }
            }
            
            if (isset($data[OrderInterface::CUSTOMER_PREFIX])) {
                $customer->setPrefix(
                    $data[OrderInterface::CUSTOMER_PREFIX]
                );
            }

            if (isset($data[OrderInterface::CUSTOMER_LASTNAME])) {
                $customer->setLastname($data[OrderInterface::CUSTOMER_LASTNAME]);
            }
            
            if (isset($data[OrderInterface::CUSTOMER_MIDDLENAME])) {
                $customer->setMiddlename(
                    $data[OrderInterface::CUSTOMER_MIDDLENAME]
                );
            }

            if (isset($data[OrderInterface::CUSTOMER_FIRSTNAME])) {
                $customer->setFirstname(
                    $data[OrderInterface::CUSTOMER_FIRSTNAME]
                );
            }

            if (isset($data[OrderInterface::CUSTOMER_SUFFIX])) {
                $customer->setSuffix(
                    $data[OrderInterface::CUSTOMER_SUFFIX]
                );
            }

            if (isset($data[OrderInterface::CUSTOMER_DOB])) {
                $customer->setDob($data[OrderInterface::CUSTOMER_DOB]);
            }

            if (isset($data[OrderInterface::CUSTOMER_TAXVAT])) {
                $customer->setTaxvat($data[OrderInterface::CUSTOMER_TAXVAT]);
            }

            if (isset($data[OrderInterface::CUSTOMER_EMAIL])) {
                $customer->setEmail(
                    $data[OrderInterface::CUSTOMER_EMAIL]
                );
            }
            
            if (isset($data['customer_disable_auto_group_change'])) {
                $customer->setDisableAutoGroupChange(
                    $data['customer_disable_auto_group_change']
                );
            }
            
            if (isset($data['customer_confirmation'])) {
                $customer->setConfirmation(
                    $data['customer_confirmation']
                );
            }

            $websiteId = $order->getStore()->getWebsiteId();
            $storeId = $order->getStore()->getId();
            $storeName = $order->getStore()->getName();
            $customer->setWebsiteId($websiteId);
            $customer->setStoreId($storeId);
            $customer->setCreatedIn($storeName);

            if (!$order->getCustomerIsGuest()) {
                $this->repository->save($customer);
                $customer = $this->repository->get(
                    $customer->getEmail()
                );
            }
        }

        $customerId = $customer->getId();
        $customerEmail = $customer->getEmail();
        if (!$this->cache->getCustomer($customerId) &&
            !$this->cache->getCustomer($customerEmail)
        ) {
            $this->cache->addCustomer($customerId, $customer);
            $this->cache->addCustomer($customerEmail, $customer);
        }

        return $customer;
    }

    /**
     * @param string|int $value
     * @return bool|int
     */
    private function getGender($value)
    {
        if (strtolower(trim($value)) === 'male') {
            return 1;
        }

        if (strtolower(trim($value)) === 'female') {
            return 2;
        }

        if (is_numeric($value)
            && ((int)$value === 1 || (int)$value === 2)
        ) {
            return (int)$value;
        }

        return false;
    }
}
