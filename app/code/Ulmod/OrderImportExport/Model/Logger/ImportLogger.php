<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Logger;

use Ulmod\OrderImportExport\Model\Config;
use Ulmod\OrderImportExport\Model\ImportlogFactory;

class ImportLogger
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ImportlogFactory
     */
    protected $importlogFactory;

    /**
     * @param Config $config
     * @param ImportlogFactory $importlogFactory
     */
    public function __construct(
        Config $config,
        ImportlogFactory $importlogFactory
    ) {
        $this->importlogFactory = $importlogFactory;
        $this->config = $config;
    }
    
    /**
     * log import
     *
     * @param $createdAt
     * @param $username
     * @param $filename
     * @param $executionTime
     * @param $type     
     * @param $status
     * @param $message
     */
    public function log($createdAt, $username, $filename, $executionTime, $type, $status, $message)
    {
        if (!$this->config->isImportLogEnabled()) {
            return;
        }
        
        /** @var ImportlogFactory $importlogMessage */
        $importlogMessage = $this->importlogFactory->create();
        $importlogMessage->setData([
            'created_at'        => $createdAt,
            'username'          => $username,
            'filename'          => $filename,
            'execution_time'    => $executionTime,
            'type'              => $type,               
            'status'            => $status,
            'message'           => $message
        ]);

        $importlogMessage->save();
    }
}
