<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Item as OrderItemModel;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer as CustomerModel;

class Order extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @param array $data
     * @param OrderInterface|OrderModel $order
     *
     * @return $this
     */
    public function process(array $data, OrderInterface $order)
    {
        $order->setSendEmail(false);
        $order->setCanSendNewEmailFlag(false);

        if ($order->getIncrementId()) {
            $this->prepareIncrementId($order);
        } else {
            $order->unsetData(OrderInterface::INCREMENT_ID);
        }

        $this->prepareData($order);

        return $this;
    }

    /**
     * @param OrderInterface|OrderModel $order
     */
    private function prepareData(OrderInterface &$order)
    {
        $this->removeExcludedFields($order);

        $items = $order->getAllItems();

        if ($order->getDiscountAmount() === null) {
            $amount = 0;

            /** @var OrderItemInterface|OrderItemModel $item $item */
            foreach ($items as &$item) {
                $amount += (float)$item->getDiscountAmount();
            }

            $order->setDiscountAmount(0 - $amount);
        }

        if ($order->getTaxAmount() === null) {
            $amount = 0;

            /** @var OrderItemInterface|OrderItemModel $item $item */
            foreach ($items as &$item) {
                $amount += (float)$item->getTaxAmount();
            }

            $order->setTaxAmount($amount);
        }
    
        if ($order->getSubtotal() === null) {
            $amount = 0;

            /** @var OrderItemInterface|OrderItemModel $item $item */
            foreach ($items as &$item) {
                $amount += (float)$item->getRowTotal();
            }

            $order->setSubtotal($amount);
        }

        if ($order->getSubtotalInclTax() === null) {
            $amount = 0;

            /** @var OrderItemInterface|OrderItemModel $item $item */
            foreach ($items as &$item) {
                $amount += (float)$item->getRowTotalInclTax();
            }

            $order->setSubtotalInclTax($amount);
        }

        if ($order->getDiscountTaxCompensationAmount() === null) {
            $amount = 0;

            /** @var OrderItemInterface|OrderItemModel $item $item */
            foreach ($items as &$item) {
                $amount += (float)$item->getDiscountTaxCompensationAmount();
            }

            $order->setDiscountTaxCompensationAmount($amount);
        }
    
        if ($order->getWeight() === null) {
            $amount = 0;

            /** @var OrderItemInterface|OrderItemModel $item $item */
            foreach ($items as &$item) {
                if (!$item->getIsVirtual()) {
                    $amount += (float)$item->getWeight();
                }
            }

            $order->setWeight($amount);
        }

        $subtotal = $order->getSubtotal();
        $grandTotal = $order->getGrandTotal();
        if ($subtotal && ($grandTotal === null)) {
            $order->setGrandTotal(
                $subtotal +
                $order->getTaxAmount() +
                $order->getShippingAmount() +
                $order->getDiscountAmount()
            );
        }

        if ($order->getShippingAmount() === null) {
            $order->setShippingAmount(0);
        }

        if ($order->getShippingTaxAmount() === null) {
            $order->setShippingTaxAmount(0);
        }
        if ($order->getBaseGrandTotal() === null) {
            $order->setBaseGrandTotal($order->getGrandTotal());
        }
        
        if ($order->getBaseDiscountAmount() === null) {
            $order->setBaseDiscountAmount($order->getDiscountAmount());
        }

        if ($order->getBaseDiscountTaxCompensationAmount() === null) {
            $order->setBaseDiscountTaxCompensationAmount(
                $order->getDiscountTaxCompensationAmount()
            );
        }
        
        if ($order->getBaseShippingAmount() === null) {
            $order->setBaseShippingAmount($order->getShippingAmount());
        }

        if ($order->getBaseShippingDiscountAmount() === null) {
            $order->setBaseShippingDiscountAmount(
                $order->getShippingDiscountAmount()
            );
        }

        if ($order->getBaseShippingDiscountTaxCompensationAmnt() === null) {
            $order->setBaseShippingDiscountTaxCompensationAmnt(
                $order->getShippingDiscountTaxCompensationAmount()
            );
        }

        if ($order->getBaseShippingTaxAmount() === null) {
            $order->setBaseShippingTaxAmount($order->getShippingTaxAmount());
        }

        if ($order->getBaseShippingInclTax() === null) {
            $order->setBaseShippingInclTax($order->getShippingInclTax());
        }

        if ($order->getBaseTaxAmount() === null) {
            $order->setBaseTaxAmount($order->getTaxAmount());
        }

        if ($order->getBaseSubtotal() === null) {
            $order->setBaseSubtotal($order->getSubtotal());
        }

        if ($order->getBaseSubtotalInclTax() === null) {
            $order->setBaseSubtotalInclTax($order->getSubtotalInclTax());
        }

        if ($order->getBaseTotalPaid() === null) {
            $order->setBaseTotalPaid($order->getTotalPaid());
        }
    
        if ($order->getBaseTotalQtyOrdered() === null) {
            $order->setBaseTotalQtyOrdered($order->getTotalQtyOrdered());
        }

        if ($order->getBaseTotalDue() === null) {
            $order->setBaseTotalDue($order->getTotalDue());
        }

        /** @var CustomerInterface|CustomerModel $customer */
        $customer = $order->getCustomer();

        if ($order->getCustomerPrefix() === null) {
            $order->setCustomerPrefix($customer->getPrefix());
        }
        
        if ($order->getCustomerFirstname() === null) {
            $order->setCustomerFirstname($customer->getFirstname());
        }

        if ($order->getCustomerMiddlename() === null) {
            $order->setCustomerMiddlename($customer->getMiddlename());
        }

        if ($order->getCustomerLastname() === null) {
            $order->setCustomerLastname($customer->getLastname());
        }
        
        if ($order->getCustomerSuffix() === null) {
            $order->setCustomerSuffix($customer->getSuffix());
        }

        if ($order->getCustomerDob() === null) {
            $order->setCustomerDob($customer->getDob());
        }
        
        if ($order->getStoreName() === null) {
            $order->setStoreName(
                $order->getStore()->getName()
            );
        }
    
        if ($order->getCustomerTaxvat() === null) {
            $order->setCustomerTaxvat(
                $customer->getTaxvat()
            );
        }

        $store = $order->getStore();
        if (!$order->getStoreCurrencyCode()) {
            $order->setStoreCurrencyCode(
                $store->getCurrentCurrencyCode()
            );
        }

        if (!$order->getBaseCurrencyCode()) {
            $order->setBaseCurrencyCode(
                $store->getBaseCurrencyCode()
            );
        }

        if (!$order->getOrderCurrencyCode()) {
            $order->setOrderCurrencyCode(
                $order->getStoreCurrencyCode()
            );
        }

        if (!$order->hasData(OrderInterface::STATUS)) {
            $order->setData(
                OrderInterface::STATUS,
                $order::STATE_PROCESSING
            );
        }
    
        if (!$order->hasData(OrderInterface::STATE)) {
            $order->setData(
                OrderInterface::STATE,
                $order::STATE_PROCESSING
            );
        }
    }

    /**
     * Prepare increment ID
     *
     * @param OrderInterface $order
     */
    private function prepareIncrementId(OrderInterface &$order)
    {
        $resource = $order->getResource();
        $adapter  = $resource->getConnection();

        $select = $adapter->select()->from(
            $resource->getMainTable(),
            ['count' => 'COUNT(*)']
        );
        $select->where(
            OrderInterface::INCREMENT_ID . ' = ?',
            $order->getIncrementId()
        );
        $result = $adapter->fetchOne($select);
        if ($result) {
            $order->unsetData(
                OrderInterface::INCREMENT_ID
            );
        }
    }
}
