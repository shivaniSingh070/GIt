<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Logger;

use Ulmod\OrderImportExport\Model\Config;
use Ulmod\OrderImportExport\Model\ExportlogFactory;

class ExportLogger
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ExportlogFactory
     */
    protected $exportlogFactory;

    /**
     * @param Config $config
     * @param ExportlogFactory $exportlogFactory
     */
    public function __construct(
        Config $config,
        ExportlogFactory $exportlogFactory
    ) {
        $this->exportlogFactory = $exportlogFactory;
        $this->config = $config;
    }
    
    /**
     * log export
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
        if (!$this->config->isExportLogEnabled()) {
            return;
        }
        
        /** @var ExportlogFactory $exportlogMessage */
        $exportlogMessage = $this->exportlogFactory->create();
        $exportlogMessage->setData([
            'created_at'        => $createdAt,
            'username'          => $username,
            'filename'          => $filename,
            'execution_time'    => $executionTime,
            'type'              => $type,            
            'status'            => $status,
            'message'           => $message
        ]);

        $exportlogMessage->save();
    }
}
