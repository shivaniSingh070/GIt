<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model;

use Ulmod\OrderImportExport\Model\Data as ModelData;
use Ulmod\OrderImportExport\Model\File as ModelFile;
use Ulmod\OrderImportExport\Model\Db\Collection as CollectionDbModel;
use Ulmod\OrderImportExport\Framework\File\CsvFactory;
use Ulmod\OrderImportExport\Model\Data\Formatter;
use Ulmod\OrderImportExport\Api\Data\ExportConfigInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Filesystem\Io\File as Iofile;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
        
class Exporter implements \Ulmod\OrderImportExport\Api\ExporterInterface
{
    /**
     * @var ModelData
     */
    private $modelData;

    /**
     * @var ModelFile
     */
    private $modelFile;

    /**
     * @var CollectionDbModel
     */
    private $collectionDbModel;

    /**
     * @var ExportConfigInterface
     */
    private $config;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Iofile
     */
    private $file;

    /**
     * @var \Ulmod\OrderImportExport\Framework\File\Csv
     */
    private $csv;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $frontColumns;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param ModelData $modelData
     * @param ModelFile $modelFile
     * @param CollectionDbModel $collectionDbModel
     * @param CsvFactory $csvFactory
     * @param Formatter $formatter
     * @param ExportConfigInterface $config
     * @param CollectionFactory $collectionFactory
     * @param Iofile $file
     * @param LoggerInterface $logger
     * @param array $frontColumns
     */
    public function __construct(
        ModelData $modelData,
        ModelFile $modelFile,
        CollectionDbModel $collectionDbModel,
        CsvFactory $csvFactory,
        Formatter $formatter,
        ExportConfigInterface $config,
        CollectionFactory $collectionFactory,
        Iofile $file,
        LoggerInterface $logger,
        $frontColumns = []
    ) {
        $this->modelData            = $modelData;
        $this->modelFile        = $modelFile;
        $this->collectionDbModel  = $collectionDbModel;
        $this->csv               = $csvFactory->create();
        $this->formatter         = $formatter;
        $this->config            = $config;
        $this->collectionFactory = $collectionFactory;
        $this->file              = $file;
        $this->logger            = $logger;
        $this->frontColumns      = $frontColumns;
    }

    /**
     * @param bool $forceReload
     *
     * @return array
     * @throws LocalizedException
     */
    public function getData($forceReload = false)
    {
        if (!$this->data || $forceReload) {
            /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
            $collection = $this->collectionFactory->create();

            $filters = [];
            $dateFrom = $this->config->getFrom();
            if ($dateFrom) {
                $filters['from'] = $this->collectionDbModel
                    ->getFromDateFilter($dateFrom);
            }
            
            $dateTo = $this->config->getTo();
            if ($dateTo) {
                $filters['to'] = $this->collectionDbModel
                    ->getToDateFilter($dateTo);
            }

            if ($filters) {
                $collection->addAttributeToFilter(
                    'created_at',
                    $filters
                );
            }

            $array = [];

            /** @var \Magento\Sales\Model\Order $order */
            foreach ($collection as $order) {
                $storeCode = $order->getStore()->getCode();
                $order->setStoreCode($storeCode);
                $order->getStatusHistories();
                
                $payment = $order->getPayment();
                if ($payment) {
                    $paymentData = $payment->getData();
                    $additionalInfo = $payment->getAdditionalInformation();
                    if (is_array($additionalInfo)) {
                        foreach ($additionalInfo as $key => $value) {
                            $paymentData[$key] = $value;
                        }
                    }
                    ksort($paymentData);
                    
                    $this->modelData->addPrefix(
                        'payment_',
                        $paymentData
                    );
                    
                    $order->addData($paymentData);
                }
                
                $shippingAddress = $order->getShippingAddress();
                if ($shippingAddress) {
                    $this->modelData->addPrefix(
                        'shipping_',
                        $shippingAddress
                    );
                    $order->addData($shippingAddress->getData());
                }

                $billingAddress = $order->getBillingAddress();
                if ($billingAddress) {
                    $this->modelData->addPrefix(
                        'billing_',
                        $billingAddress
                    );
                    $order->addData($billingAddress->getData());
                }
                
                $items = [];

                foreach ($order->getAllItems() as $item) {
                    $options = $item->getProductOptions();
                    if (is_array($options)) {
                        $item->setData('product_options', $options);
                    }

                    $parentItemId = $item->getParentItemId();
                    if (!$parentItemId) {
                        $items[$item->getId()] = $item;
                    } else {
                        if (isset($items[$parentItemId])) {
                            $bundleItems = $items[$parentItemId]->getBundleItems();
                            if (!is_array($items[$parentItemId]->getBundleItems())) {
                                $bundleItems = [];
                            }

                            $bundleItems[] = $item;
                            $items[$parentItemId]->setBundleItems($bundleItems);
                        }
                    }
                }

                $order->setItems($items);

                $orderData = $order->getData();
                ksort($orderData);

                $newOrderData = [];
                foreach ($this->frontColumns as $key) {
                    if (isset($orderData[$key])) {
                        $newOrderData[$key] = $orderData[$key];
                        unset($orderData[$key]);
                    }
                }

                $newOrderData += $orderData;

                $order->setData($newOrderData);

                $array[] = $this->formatter->format($order);
            }

            $this->modelData->equalizeArrayKeys($array);
            $this->modelData->addHeadersRowToArray($array);

            $this->data = $array;
        }

        return $this->data;
    }

    /**
     * Get directory to which the file will be exported
     *
     * @param bool $absolute
     * @return string
     */
    public function getDirectory($absolute = true)
    {
        $directory = $this->config->getDirectory();
        if ($absolute === true) {
            $directory = $this->modelFile
                ->getAbsolutePath($directory);
        }

        return $directory;
    }

    /**
     * Exports to csv file and returns path of csv file
     *
     * @param bool $forceReload
     * @return string
     * @throws \Exception
     */
    public function export($forceReload = false)
    {
        $filepath = $this->getFilepath(true);
        $this->file->checkAndCreateFolder(
            $this->modelFile->getDirname($filepath)
        );

        $data = $this->getData($forceReload);
        
        $enclosure = $this->config->getEnclosure();
        $this->csv->setEnclosure($enclosure);
        
        $delimiter = $this->config->getDelimiter();
        $this->csv->setDelimiter($delimiter);
        
        $this->csv->saveData($filepath, $data);

        return $filepath;
    }
    
    /**
     * Get file path
     *
     * @param bool $absolute
     * @return string
     */
    public function getFilepath($absolute = true)
    {
        $filename = $this->config->getFilename();
        return $this->modelFile->assembleFilepath(
            [
                $this->getDirectory($absolute),
                $filename
            ],
            $absolute
        );
    }
}
