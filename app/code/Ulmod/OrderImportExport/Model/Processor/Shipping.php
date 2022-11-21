<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\DataObject;

class Shipping extends AbstractProcessor implements ProcessorInterface
{
    /**
     * Process shipping
     *
     * @param array $data
     * @param OrderInterface|DataObject $order
     * @return $this
     */
    public function process(array $data, OrderInterface $order)
    {
        $this->removeExcludedFields($order);

        $shippingMethodData = $order->getData('shipping_method');
        if ($shippingMethodData) {
            $shippingMethod = trim(strtolower(
                $shippingMethodData
            ));
            $shippingMethod = str_replace(
                ' ',
                '_',
                $shippingMethod
            );
            $shippingMethod = preg_replace(
                "/[^A-Za-z0-9_]/",
                '',
                $shippingMethod
            );
            $order->setData(
                'shipping_method',
                $shippingMethod
            );
        } else {
            $order->setData(
                'shipping_method',
                'placeholder_method'
            );
        }

        return $this;
    }
}
