<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Sales\Model\Order\Payment as OrderPayment;
    
class Transaction extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @param BuilderInterface $transactionBuilder
     * @param TransactionRepositoryInterface $transactionRepository
     * @param array $excludedFields
     */
    public function __construct(
        BuilderInterface $transactionBuilder,
        TransactionRepositoryInterface $transactionRepository,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->transactionBuilder    = $transactionBuilder;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Process transaction
     *
     * @param array $data
     * @param OrderInterface|\Magento\Sales\Model\Order $order
     * @return $this
     */
    public function process(array $data, OrderInterface $order)
    {
        /** @var OrderPaymentInterface|OrderPaymentt $payment */
        $payment = $order->getPayment();
        
        $invoice = $order->getInvoice();
        if ($payment && $invoice) {
            $transactionBuilder = $this->transactionBuilder;
            $transactionBuilder->reset();
            $transactionBuilder->setOrder($order);
            $transactionBuilder->setPayment($payment);
            $transactionBuilder->setSalesDocument($invoice);

            $transactionId = $payment->getTransactionId();
            $lastTransId = $payment->getLastTransId();
            if ($transactionId) {
                $transactionBuilder->setTransactionId(
                    $transactionId
                );
            } else {
                $transactionBuilder->setTransactionId(
                    $lastTransId
                );
            }

            $transaction = $transactionBuilder->build(
                PaymentTransaction::TYPE_CAPTURE
            );

            if ($transaction) {
                $this->transactionRepository->save(
                    $transaction
                );
            }
        }

        return $this;
    }
}
