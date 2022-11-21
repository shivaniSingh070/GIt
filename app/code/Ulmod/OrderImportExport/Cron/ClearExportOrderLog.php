<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Cron;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Ulmod\OrderImportExport\Model\Config;
use Ulmod\OrderImportExport\Model\ResourceModel\Exportlog\Collection;
use Ulmod\OrderImportExport\Model\ResourceModel\Exportlog\CollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

class ClearExportOrderLog
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
    protected $exportlogCollectionFactory;
    
    /**
     * @var DateTime
     */
    protected $date;
    
    /**
     * @param LoggerInterface $logger
     * @param CollectionFactory $exportlogCollectionFactory
     * @param DateTime $date
     * @param Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $exportlogCollectionFactory,
        DateTime $date,
        Config $config
    ) {
    
        $this->logger = $logger;
        $this->exportlogCollectionFactory = $exportlogCollectionFactory;
        $this->date = $date;
        $this->config = $config;
    }
    
    /**
     * Clear export Log after X day(s)
     *
     * @return $this
     */
    public function execute()
    {
        $day = $this->config->getClearExportOrderLogDays();

        if (isset($day) && $day > 0) {
            $timeEnd = strtotime($this->date->date()) - $day * 24 * 60 * 60;

            /** @var Collection $exportLogs */
            $exportLogs = $this->exportlogCollectionFactory->create()
                ->addFieldToFilter(
                    'created_at',
                    ['lteq' => date('Y-m-d H:i:s', $timeEnd)]
                );
            foreach ($exportLogs as $exportLog) {
                try {
                    $exportLog->delete();
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }

        return $this;
    }
}
