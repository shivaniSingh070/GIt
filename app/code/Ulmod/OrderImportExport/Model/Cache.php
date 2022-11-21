<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model;

use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Customer\Model\Group as GroupModel;
use Magento\Customer\Model\Customer as CustomerModel;

class Cache
{
    /**
     * @var array|WebsiteInterface[]
     */
    private $websites = [];

    /**
     * @var array|CustomerInterface[]
     */
    private $customers = [];

    /**
     * @var array|StoreInterface[]
     */
    private $stores = [];

    /**
     * @var array|GroupInterface[]
     */
    private $customerGroups = [];

    /**
     * @var array|PaymentMethodInterface[]
     */
    private $paymentMethods = [];

    /**
     * @var array|AddressInterface[]
     */
    private $addresses = [];
    
    /**
     * Store website objects using website id or website code
     *
     * @param string|int $key
     * @param WebsiteInterface $object
     * @return $this
     */
    public function addWebsite($key, WebsiteInterface $object)
    {
        if ($key !== null && $key !== '') {
            $this->websites[$key] = $object;
        }

        return $this;
    }

    /**
     * Store store objects using store id or store code
     *
     * @param string|int $key
     * @param StoreInterface $object
     * @return $this
     */
    public function addStore($key, StoreInterface $object)
    {
        if ($key !== null && $key !== '') {
            $this->stores[$key] = $object;
        }

        return $this;
    }

    /**
     * @param string|int $key
     * @return bool|WebsiteInterface|\Magento\Store\Model\Website
     */
    public function getWebsite($key)
    {
        return array_key_exists($key, $this->websites)
            ? $this->websites[$key] : false;
    }

    /**
     * @param string|int $key
     * @return bool|StoreInterface|\Magento\Store\Model\Store
     */
    public function getStore($key)
    {
        return array_key_exists($key, $this->stores)
            ? $this->stores[$key] : false;
    }

    /**
     * @param string|int $key
     * @return bool|CustomerInterface|CustomerModel
     */
    public function getCustomer($key)
    {
        return array_key_exists($key, $this->customers)
            ? $this->customers[$key] : false;
    }

    /**
     * Store customer objects by email or customer id
     *
     * @param string|int $key
     * @param CustomerInterface $object
     * @return $this
     */
    public function addCustomer($key, CustomerInterface $object)
    {
        if ($key !== null && $key !== '') {
            $this->customers[$key] = $object;
        }

        return $this;
    }
    
    /**
     * @param $key
     * @param GroupModel $object
     * @return $this
     */
    public function addCustomerGroup($key, GroupModel $object)
    {
        if ($key !== null && $key !== '') {
            $this->customerGroups[$key] = $object;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCustomerGroups()
    {
        return !empty($this->customerGroups);
    }

    /**
     * @param string|int $key
     * @return bool|GroupModel
     */
    public function getCustomerGroup($key)
    {
        return array_key_exists($key, $this->customerGroups)
            ? $this->customerGroups[$key] : false;
    }
    
    /**
     * Store address by customer id & stringified address
     * OR customer id and address id
     *
     * @param int $customerId
     * @param int|string $key
     * @param AddressInterface $object
     * @return $this
     */
    public function addAddress(
        $customerId,
        $key,
        AddressInterface $object
    ) {
        if ($key !== null && $key !== '') {
            $this->addresses[$customerId][$key] = $object;
        }

        return $this;
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function hasAddresses($customerId)
    {
        return array_key_exists($customerId, $this->addresses);
    }

    /**
     * Get address by customer id and stringified address
     * OR customer id and address id
     *
     * @param int $customerId
     * @param int|string $key
     * @return bool|AddressInterface
     */
    public function getAddress($customerId, $key)
    {
        if (isset($this->addresses[$customerId][$key])) {
            return $this->addresses[$customerId][$key];
        }

        return false;
    }
    
    /**
     * @param string|int $key
     * @param PaymentMethodInterface $object
     * @return $this
     */
    public function addPaymentMethod(
        $key,
        PaymentMethodInterface $object
    ) {
        if ($key !== null && $key !== '') {
            $this->paymentMethods[$key] = $object;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPaymentMethods()
    {
        return !empty($this->paymentMethods);
    }

    /**
     * @param string|int $key
     * @return bool|PaymentMethodInterface
     */
    public function getPaymentMethod($key)
    {
        return array_key_exists($key, $this->paymentMethods)
            ? $this->paymentMethods[$key] : false;
    }
}
