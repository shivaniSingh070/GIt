<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model;

use Ulmod\OrderImportExport\Api\ImporterInterface;
use Ulmod\OrderImportExport\Exception\ImportException;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ulmod\OrderImportExport\Api\Data\ImportConfigInterface;
use Ulmod\OrderImportExport\Model\Parser\ParserInterface;
use Magento\Framework\DataObject;
use Ulmod\OrderImportExport\Model\Data\Mapper;
use Ulmod\OrderImportExport\Model\Data\Sanitizer;
use Ulmod\OrderImportExport\Model\Data as ModelData;
use Magento\Sales\Api\Data\OrderInterface as DataOrderInterface;
use Ulmod\OrderImportExport\Model\Data\Validator as OrderDataValidator;
use Ulmod\OrderImportExport\Model\Data\Validator as OrderItemDataValidator;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as StoreProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as CustomerProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as BillingAddressProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as ShippingAddressProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as PaymentProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as TransactionProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as ShippingProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as ItemProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as StatusHistoryProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as OrderProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as InvoiceProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as ShipmentProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface as CreditMemoProcessor;
        
class Importer implements ImporterInterface
{
    /**
     * @var ModelData
     */
    private $modelData;

    /**
     * @var Sanitizer
     */
    private $sanitizer;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var OrderDataValidator
     */
    private $orderDataValidator;

    /**
     * @var ParserInterface
     */
    private $orderItemParser;

    /**
     * @var OrderItemDataValidator
     */
    private $orderItemDataValidator;
    
    /**
     * @var StoreProcessor
     */
    private $storeProcessor;

    /**
     * @var BillingAddressProcessor
     */
    private $billingAddressProcessor;
    
    /**
     * @var CustomerProcessor
     */
    private $customerProcessor;

    /**
     * @var ShippingAddressProcessor
     */
    private $shippingAddressProcessor;

    /**
     * @var TransactionProcessor
     */
    private $transactionProcessor;

    /**
     * @var PaymentProcessor
     */
    private $paymentProcessor;

    /**
     * @var ShipmentProcessor
     */
    private $shippingProcessor;

    /**
     * @var StatusHistoryProcessor
     */
    private $statusHistoryProcessor;

    /**
     * @var ItemProcessor
     */
    private $itemProcessor;
    
    /**
     * @var OrderProcessor
     */
    private $orderProcessor;

    /**
     * @var ShipmentProcessor
     */
    private $shipmentProcessor;

    /**
     * @var InvoiceProcessor
     */
    private $invoiceProcessor;
    
    /**
     * @var CreditMemoProcessor
     */
    private $creditMemoProcessor;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderInterfaceFactory|\Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var array
     */
    private $excludedFields;

    /**
     * @param FileModel $fileModel
     * @param ModelData $modelData
     * @param Sanitizer $sanitizer
     * @param Mapper $mapper
     * @param OrderDataValidator $orderDataValidator
     * @param OrderItemDataValidator $orderItemDataValidator
     * @param ParserInterface $orderItemParser
     * @param StoreProcessor $storeProcessor
     * @param ImportConfigInterface $config
     * @param CustomerProcessor $customerProcessor
     * @param PaymentProcessor $paymentProcessor
     * @param TransactionProcessor $transactionProcessor
     * @param BillingAddressProcessor $billingAddressProcessor
     * @param ShippingAddressProcessor $shippingAddressProcessor
     * @param ShipmentProcessor $shippingProcessor
     * @param ItemProcessor $itemProcessor
     * @param InvoiceProcessor $invoiceProcessor
     * @param ShipmentProcessor $shipmentProcessor
     * @param CreditMemoProcessor $creditMemoProcessor
     * @param StatusHistoryProcessor $statusHistoryProcessor
     * @param OrderProcessor $orderProcessor
     * @param OrderInterfaceFactory $orderFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param array $excludedFields
     */
    public function __construct(
        ModelData $modelData,
        Sanitizer $sanitizer,
        Mapper $mapper,
        OrderDataValidator $orderDataValidator,
        OrderItemDataValidator $orderItemDataValidator,
        ParserInterface $orderItemParser,
        StoreProcessor $storeProcessor,
        ImportConfigInterface $config,
        CustomerProcessor $customerProcessor,
        PaymentProcessor $paymentProcessor,
        TransactionProcessor $transactionProcessor,
        BillingAddressProcessor $billingAddressProcessor,
        ShippingAddressProcessor $shippingAddressProcessor,
        ShipmentProcessor $shippingProcessor,
        ItemProcessor $itemProcessor,
        InvoiceProcessor $invoiceProcessor,
        ShipmentProcessor $shipmentProcessor,
        CreditMemoProcessor $creditMemoProcessor,
        StatusHistoryProcessor $statusHistoryProcessor,
        OrderProcessor $orderProcessor,
        OrderInterfaceFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        array $excludedFields = []
    ) {
        $this->modelData = $modelData;
        $this->sanitizer = $sanitizer;
        $this->mapper = $mapper;
        $this->orderDataValidator = $orderDataValidator;
        $this->orderItemDataValidator = $orderItemDataValidator;
        $this->orderItemParser = $orderItemParser;
        $this->storeProcessor = $storeProcessor->setConfig($config);
        $this->customerProcessor = $customerProcessor->setConfig($config);
        $this->paymentProcessor = $paymentProcessor->setConfig($config);
        $this->transactionProcessor = $transactionProcessor->setConfig($config);
        $this->billingAddressProcessor  = $billingAddressProcessor->setConfig($config);
        $this->shippingAddressProcessor = $shippingAddressProcessor->setConfig($config);
        $this->shippingProcessor = $shippingProcessor->setConfig($config);
        $this->itemProcessor  = $itemProcessor->setConfig($config);
        $this->invoiceProcessor = $invoiceProcessor->setConfig($config);
        $this->shipmentProcessor  = $shipmentProcessor->setConfig($config);
        $this->creditMemoProcessor  = $creditMemoProcessor->setConfig($config);
        $this->statusHistoryProcessor   = $statusHistoryProcessor->setConfig($config);
        $this->orderProcessor = $orderProcessor->setConfig($config);
        $this->orderFactory  = $orderFactory;
        $this->orderRepository  = $orderRepository;
        $this->excludedFields = array_keys($excludedFields);
    }

    /**
     * Import order
     *
     * @param array $data
     * @return DataOrderInterface
     * |\Magento\Sales\Model\Order
     */
    public function import(array $data)
    {
        $this->modelData->removeElements(
            $data,
            $this->excludedFields
        );
        
        $this->modelData->nullifyEmpty($data);
        
        $this->mapper->map($data);
        
        $this->sanitizer->sanitize($data);

        $validatorDatas = $this->orderDataValidator->validate($data);
        if (is_array($validatorDatas)) {
            throw new \Ulmod\OrderImportExport\Exception\ValidatorException($validatorDatas);
        }

        $data[self::KEY_PRODUCTS_ORDERED] = $this->orderItemParser->parse(
            $data[self::KEY_PRODUCTS_ORDERED]
        );

        // validate products_ordered column
        foreach ($data[self::KEY_PRODUCTS_ORDERED] as $item) {
            $validatorDatas = $this->orderItemDataValidator->validate($item);
            if (is_array($validatorDatas)) {
                throw new \Ulmod\OrderImportExport\Exception\FieldValidatorException(
                    self::KEY_PRODUCTS_ORDERED,
                    $validatorDatas
                );
            }
        }

        /** @var  \Magento\Sales\Api\Data\OrderInterface| DataObject $orderInterface */
        $orderInterface = $this->orderFactory->create();
        $orderInterface->addData($data);

        try {
            $this->storeProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\StoreException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }

        try {
            $this->customerProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\CustomerException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }

        try {
            $this->itemProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\OrderItemException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }

        try {
            $this->billingAddressProcessor->process($data, $orderInterface);
            if ($orderInterface->getIsNotVirtual()) {
                $this->shippingAddressProcessor->process($data, $orderInterface);
            }
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\AddressException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }

        try {
            $this->shippingProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\ShippingException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }

        try {
            $this->paymentProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\PaymentException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }

        try {
            $this->statusHistoryProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\StatusHistoryException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }

        try {
            $this->orderProcessor->process($data, $orderInterface);
            $this->orderRepository->save($orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\OrderException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }

        try {
            $this->invoiceProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\InvoiceException(
                ImportException::IMPORT_STATUS_YES,
                __($e->getMessage())
            );
        }

        try {
            $this->transactionProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\TransactionException(
                ImportException::IMPORT_STATUS_YES,
                __($e->getMessage())
            );
        }

        try {
            $this->shipmentProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\ShipmentException(
                ImportException::IMPORT_STATUS_YES,
                __($e->getMessage())
            );
        }

        try {
            $this->creditMemoProcessor->process($data, $orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\CreditMemoException(
                ImportException::IMPORT_STATUS_YES,
                __($e->getMessage())
            );
        }

        try {
            $this->orderRepository->save($orderInterface);
        } catch (\Exception $e) {
            throw new \Ulmod\OrderImportExport\Exception\OrderException(
                ImportException::IMPORT_STATUS_NO,
                __($e->getMessage())
            );
        }
        
        return $orderInterface;
    }
}
