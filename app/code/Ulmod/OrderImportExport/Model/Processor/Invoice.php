<?php
/*** Copyright © Ulmod. All rights reserved. **/

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Ulmod\OrderImportExport\Api\ImporterInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
        
class Invoice extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var InvoiceManagementInterface
     */
    private $service;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $repository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param InvoiceManagementInterface $service
     * @param InvoiceRepositoryInterface $repository
     * @param OrderRepositoryInterface   $orderRepository
     * @param array $excludedFields
     */
    public function __construct(
        InvoiceManagementInterface $service,
        InvoiceRepositoryInterface $repository,
        OrderRepositoryInterface $orderRepository,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->service = $service;
        $this->repository = $repository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Invoice process
     *
     * @param array  $data
     * @param OrderInterface|OrderModel $order
     * @return $this|mixed
     * @throws LocalizedException
     */
    public function process(array $data, OrderInterface $order)
    {
        $isCreateInvoice = $this->getConfig()->getCreateInvoice();
        $collectionSize = $order->getInvoiceCollection()->getSize();        
        if ($isCreateInvoice && !$collectionSize) {
            $this->removeExcludedFields($data);

            $quantities = [];

            $productsData = $data[ImporterInterface::KEY_PRODUCTS_ORDERED];

            /** @var OrderItemInterface|\Magento\Sales\Model\Order\Item $item */
            foreach ($order->getAllItems() as $item) {
                $key  = $item->getData('key');
                $parentKeyData = $item->getData('parent_key');

                if ($parentKeyData && isset($productsData[$parentKeyData]['bundle_items'][$key][OrderItemInterface::QTY_INVOICED])) {
                    $quantity = $productsData[$parentKeyData]['bundle_items'][$key][OrderItemInterface::QTY_INVOICED];
                } elseif (isset($productsData[$key][OrderItemInterface::QTY_INVOICED])) {
                    $quantity = $productsData[$key][OrderItemInterface::QTY_INVOICED];
                } else {
                    $quantity = $item->getQtyToInvoice();
                }

                $quantity = (float)$quantity;
                if (!$quantity) {
                    continue;
                }

                $quantities[$item->getId()] = $quantity;
            }

            if ($quantities) {
                /** @var \Magento\Sales\Model\Order\Invoice $invoice */
                $invoice = $this->service->prepareInvoice(
                    $order, $quantities
                );
                
                $discountDescr = $order->getDiscountDescription();                  
                $invoice->setDiscountDescription($discountDescr);
                
                $invoice->setDiscountAmount($order->getDiscountAmount());
                
                $baseDiscountAmount = $order->getBaseDiscountAmount();                  
                $invoice->setBaseDiscountAmount($baseDiscountAmount);
                
                $invoice->setDiscountTaxCompensationAmount(
                    $order->getDiscountTaxCompensationAmount()
                );
                
                $baseDiscountTax = $order->getBaseDiscountTaxCompensationAmount();              
                $invoice->setBaseDiscountTaxCompensationAmount($baseDiscountTax);
                
                $invoice->setShippingDiscountTaxCompensationAmount(
                    $order->getShippingDiscountTaxCompensationAmount()
                );
                
                $baseShipDiscountTax = $order->getBaseShippingDiscountTaxCompensationAmnt();
                $invoice->setBaseShippingDiscountTaxCompensationAmnt($baseShipDiscountTax);

                if ($invoice->getItems()) {
                    $grandTotal = $invoice->getGrandTotal();
                    $totalPaid = $order->getTotalPaid();                    
                    if (round($grandTotal, 4) === round($totalPaid, 4)) {
                        $invoice->setState($invoice::STATE_PAID);
                    }

                    $payment = $order->getPayment();
                    $transactionId = $payment->getCcTransId();
                    if (!$transactionId) {
                        $transactionId = $payment->getLastTransId();
                    }

                    $invoice->setTransactionId($transactionId);
                    $invoice->setSendEmail(false);
                    
                    $invoice->register();
                    $invoice->pay();
                    
                    $this->repository->save($invoice);
                    
                    $this->orderRepository->save($order);
                }

                $order->setInvoice($invoice);
            }
        }

        return $this;
    }
}
