<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Cron;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Ulmod\OrderImportExport\Model\Config;
use Ulmod\OrderImportExport\Model\ResourceModel\Importlog\Collection;
use Ulmod\OrderImportExport\Model\ResourceModel\Importlog\CollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

class ClearImportOrderLog
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * @var CollectionFactory
     */
    protected $importlogCollectionFactory;
    
    /**
     * @var DateTime
     */
    protected $date;
    
    /**
     * @param LoggerInterface $logger
     * @param CollectionFactory $importlogCollectionFactory
     * @param DateTime $date
     * @param Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $importlogCollectionFactory,
        DateTime $date,
        Config $config
    ) {
        $this->logger = $logger;
        $this->importlogCollectionFactory = $importlogCollectionFactory;
        $this->date = $date;
        $this->config = $config;
    }
    
    /**
     * Clear Import order Log after X day(s)
     *
     * @return $this
     */
    public function execute()
    {
        $day = $this->config->getClearImportOrderLogDays();

        if (isset($day) && $day > 0) {
            $timeEnd = strtotime($this->date->date()) - $day * 24 * 60 * 60;

            /** @var Collection $importLogs */
            $importLogs = $this->importlogCollectionFactory->create()
                ->addFieldToFilter(
                    'created_at',
                    ['lteq' => date('Y-m-d H:i:s', $timeEnd)]
                );
            foreach ($importLogs as $importLog) {
                try {
                    $importLog->delete();
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }

        return $this;
    }
}
