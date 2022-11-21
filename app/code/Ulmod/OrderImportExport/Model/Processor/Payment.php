<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;

class Payment extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var OrderPaymentInterfaceFactory
     */
    private $orderPaymentFactory;

    /**
     * @param OrderPaymentInterfaceFactory $orderPaymentFactory
     * @param array $excludedFields
     */
    public function __construct(
        OrderPaymentInterfaceFactory $orderPaymentFactory,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->orderPaymentFactory = $orderPaymentFactory;
    }

    /**
     * Process payment
     *
     * @param array $data
     * @param OrderInterface $order
     * @return $this|mixed
     */
    public function process(array $data, OrderInterface $order)
    {
        $paymentData = [];
        foreach ($data as $key => &$value) {
            if (strpos($key, 'payment_', 0) !== false) {
                $paymentData[str_replace('payment_', '', $key)] = $value;
            }
        }

        $this->removeExcludedFields($paymentData);
        ksort($paymentData);

        /** @var OrderPaymentInterface|OrderPayment $payment */
        $payment = $this->orderPaymentFactory->create();
        
        $payment->addData($paymentData);
        
        $payment->setData(
            $payment::ADDITIONAL_INFORMATION,
            $paymentData
        );
        
        $order->setPayment($payment);

        return $this;
    }
}
