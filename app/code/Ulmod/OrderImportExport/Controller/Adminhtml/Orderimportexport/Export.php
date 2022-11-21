<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Ulmod\OrderImportExport\Model\File as FileModel;
use Ulmod\OrderImportExport\Api\Data\ExportConfigInterface;
use Ulmod\OrderImportExport\Api\ExporterInterfaceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Psr\Log\LoggerInterface;
use Ulmod\OrderImportExport\Api\ExporterInterface;
use Ulmod\OrderImportExport\Model\Config\Export as ConfigExport;
use Ulmod\OrderImportExport\Model\ExportlogFactory;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Ulmod\OrderImportExport\Model\Logger\ExportLogger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Ulmod\OrderImportExport\Model\Config as SystemConfig;
use Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport\Exportdownload;

class Export extends \Magento\Backend\App\Action
{
    /**
     * @var FileModel
     */
    private $fileModel;

    /**
     * @var ExporterInterfaceFactory
     */
    private $exporterFactory;

    /**
     * @var ExportConfigInterface
     */
    private $config;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var ExportlogFactory
     */
    protected $exportlogFactory;

    /**
     * @var AuthSession
     */
    protected $authSession;

    /**
     * @var ExportLogger
     */
    protected $exportLogger;
   
     /**
      * @var TimezoneInterface
      */
    protected $timezoneInterface;

    /**
     * @var Exportdownload
     */
    protected $exportDownload;
    
    /**
     * @var Timezone
     */
    protected $timeZone;

    /**
     * @var SystemConfig
     */
    protected $systemConfig;

    /**
     * @var array
     */
    private $allowedExtensions = [
        'csv',
        'txt'
    ];

    /**
     * @param FileModel $fileModel
     * @param ExportConfigInterface $config
     * @param ExporterInterfaceFactory $exporterFactory
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RedirectFactory $redirectFactory
     * @param LoggerInterface $logger
     * @param ExportlogFactory $exportlogFactory
     * @param AuthSession $authSession
     * @param ExportLogger $exportLogger
     * @param TimezoneInterface $timezoneInterface
     * @param Exportdownload $exportDownload
     * @param Timezone $timeZone
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        FileModel $fileModel,
        ExportConfigInterface $config,
        ExporterInterfaceFactory $exporterFactory,
        Context $context,
        FileFactory $fileFactory,
        RedirectFactory $redirectFactory,
        LoggerInterface $logger,
        ExportlogFactory $exportlogFactory,
        AuthSession $authSession,
        ExportLogger $exportLogger,
        TimezoneInterface $timezoneInterface,
        Exportdownload $exportDownload,
        Timezone $timeZone,
        SystemConfig $systemConfig
    ) {
        parent::__construct($context);
        $this->fileModel  = $fileModel;
        $this->exporterFactory = $exporterFactory;
        $this->config = $config;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        $this->redirectFactory = $redirectFactory;
        $this->exportlogFactory = $exportlogFactory;
        $this->authSession = $authSession;
        $this->exportLogger = $exportLogger;
        $this->timezoneInterface = $timezoneInterface;
        $this->exportDownload = $exportDownload;
        $this->timeZone = $timeZone;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     * |\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {

        /** @var \Ulmod\OrderImportExport\Model\Exportlog $exportlogModel */
        $exportlogModel = $this->exportlogFactory->create();
        
        $currentAdminUser = $this->authSession->getUser();
        $currentAdminUsername = $currentAdminUser->getUsername();
        
        $exportType = 1;         
        $executionTime = '';
        $fileName = '';
        
        try {
            // set created log date here to diff excecution time
            if ($this->systemConfig->isExportLogEnabled()) {
                $exportlogModel->setCreatedAt($this->timeZone->date());
            }
            
            $orderData = $this->getRequest()
                ->getParam('export', []);
                
            $this->config->addData($orderData);
            
            // set file name based on current export time
            $basedToTimeZone = $this->getTimeBasedToTimeZone();
            $exportFilename = 'export_orders_'. $basedToTimeZone .'.csv';
            $this->config->setFilename($exportFilename);
            
            $this->config->saveConfig();

            /** @var ExporterInterface $orderExporter */
            $orderExporter = $this->exporterFactory->create(
                ['config' => $this->config]
            );

            $orderData = $orderExporter->getData();
            if (empty($orderData)) {
                throw new LocalizedException(
                    __('There are no orders that meet the search criteria.')
                );
            }
            
            $filepath = $orderExporter->export(false);
            $relativePath = $this->fileModel->getRelativePath($filepath);
            $exportFile = $this->fileFactory->create(
                $this->fileModel->getBasename($filepath),
                [
                    'type'  => 'filename',
                    'value' => $relativePath
                ],
                DirectoryList::ROOT,
                'application/csv'
            );
            
 
            $exportMessage = 'All orders have been exported successfully';
            $exportedFileSize = $this->exportDownload->getReportSize(
                $this->config->getFilename()
            );
            $exportedFileSizeKb = round($exportedFileSize / 1024, 2) . 'KB';
            $exportedFileAndSize = $relativePath .' | ' . $exportedFileSizeKb;
            
            // Log export history if enabled
            if ($this->systemConfig->isExportLogEnabled()) {
                $exportlogModel->setUsername($currentAdminUsername);
                $exportlogModel->setFilename($this->config->getFilename());
                $exportlogModel->setFilenameFullpath($exportedFileAndSize);
                $exportlogModel->setStatus(0); // 0 = SUCCESS, 1 = FAILLED
                $exportlogModel->setType($exportType);                 
                $exportlogModel->setMessage($exportMessage);
                
                $executionTime =  $this->getExecutionTime($exportlogModel->getCreatedAt());
                $exportlogModel->setExecutionTime($executionTime);
                
                $exportlogModel->save();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(
                __($e->getMessage())
            );

            $this->exportLogger->log(
                $this->timeZone->date(),
                $currentAdminUsername,
                $fileName,
                $executionTime,
                $exportType,
                1,
                $e->getMessage()
            );
            
            $redirect = $this->resultRedirectFactory->create();
            
            $redirect->setPath('*/*/exportorder');

            return $redirect;
        }

        return $exportFile;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            ConfigExport::ADMIN_RESOURCE
        );
    }

    /**
     * Get time based to timezone
     *
     * @return string
     */
    public function getTimeBasedToTimeZone()
    {
         return $this->timezoneInterface->date()
            ->format('d_m_Y_H_i_s');
    }

    /**
     * Calculate import time
     *
     * @param string $time
     * @return string
     */
    public function getExecutionTime($time)
    {
        $logTime = $this->timeZone->date($time);
        $timeDiff = $logTime->diff($this->timeZone->date());
        
        return $timeDiff->format('%H:%I:%S');
    }
}
