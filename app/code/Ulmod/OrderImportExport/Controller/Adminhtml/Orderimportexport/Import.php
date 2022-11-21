<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport;

use \Ulmod\OrderImportExport\Exception\ImportException;
use Magento\Framework\Exception\LocalizedException;
use Ulmod\OrderImportExport\Api\Data\ImportConfigInterface;
use Ulmod\OrderImportExport\Api\ImporterInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\File\Uploader as FileUploader;
use Ulmod\OrderImportExport\Framework\File\Csv as CsvProcessor;
use Psr\Log\LoggerInterface;
use Ulmod\OrderImportExport\Model\Config\Import as ConfigImport;
use Ulmod\OrderImportExport\Model\ImportlogFactory;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Ulmod\OrderImportExport\Model\Logger\ImportLogger;
use Ulmod\OrderImportExport\Model\File as FileModel;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ulmod\OrderImportExport\Model\Log\File as LogFile;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Ulmod\OrderImportExport\Model\Config as SystemConfig;
use Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport\Importdownload;

class Import extends \Magento\Backend\App\Action
{
    /**
     * @var ImportConfigInterface
     */
    private $config;

    /**
     * @var ImporterInterface
     */
    private $importer;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var CsvProcessor
     */
    private $csvProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ImportlogFactory
     */
    protected $importlogFactory;

    /**
     * @var AuthSession
     */
    protected $authSession;

    /**
     * @var ImportLogger
     */
    protected $importLogger;
    
    /**
     * @param LogFile
     */
    protected $logFile;

    /**
     * @var FileModel
     */
    private $fileModel;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var Timezone
     */
    protected $timeZone;

    /**
     * @var Importdownload
     */
    protected $importDownload;

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
     * @param ImportConfigInterface $config
     * @param ImporterInterface $importer
     * @param RedirectFactory $redirectFactory
     * @param Context $context
     * @param UploaderFactory $uploaderFactory
     * @param CsvProcessor $csvProcessor
     * @param LoggerInterface $logger
     * @param ImportlogFactory $importlogFactory
     * @param AuthSession $authSession
     * @param ImportLogger $importLogger
     * @param LogFile $logFile
     * @param FileModel $fileModel
     * @param FileFactory $fileFactory
     * @param Importdownload $importDownload
     * @param Timezone $timeZone
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        ImportConfigInterface $config,
        ImporterInterface $importer,
        RedirectFactory $redirectFactory,
        Context $context,
        UploaderFactory $uploaderFactory,
        CsvProcessor $csvProcessor,
        LoggerInterface $logger,
        ImportlogFactory $importlogFactory,
        AuthSession $authSession,
        ImportLogger $importLogger,
        LogFile $logFile,
        FileModel $fileModel,
        FileFactory $fileFactory,
        Importdownload $importDownload,
        Timezone $timeZone,
        SystemConfig $systemConfig
    ) {
        $this->config = $config;
        $this->importer = $importer;
        $this->redirectFactory = $redirectFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->csvProcessor = $csvProcessor;
        $this->logger = $logger;
        $this->importlogFactory = $importlogFactory;
        $this->authSession = $authSession;
        $this->importLogger = $importLogger;
        $this->logFile = $logFile;
        $this->fileModel  = $fileModel;
        $this->fileFactory = $fileFactory;
        $this->timeZone = $timeZone;
        $this->importDownload = $importDownload;
        $this->systemConfig = $systemConfig;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            ConfigImport::ADMIN_RESOURCE
        );
    }
    
    /**
     * @return \Magento\Framework\App\ResponseInterface
     * \Magento\Framework\Controller\Result\Redirect
     * \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('*/*/importorder');

        $imported  = 0;
        $importError = 0;
        $error = 0;
        
        $messages = [
            'error'   => [],
            'success' => []
        ];

        /** @var \Ulmod\OrderImportImport\Model\Importlog $importlogModel */
        $importlogModel = $this->importlogFactory->create();
        
        $currentAdminUser = $this->authSession->getUser();
        $currentAdminUsername = $currentAdminUser->getUsername();
        $executionTime = '';
        $fileName = '';
        $importType = 1;         
        
        try {
            // set created log date here to diff excecution time
            if ($this->systemConfig->isImportLogEnabled()) {
                $importlogModel->setCreatedAt($this->timeZone->date());
            }
            
            $data = $this->getRequest()
                ->getParam('import', []);
                
            $this->config->addData($data);
            $this->config->saveConfig();

            $files = $this->getRequest()
                ->getFiles('import');

            /** @var FileUploader $fileUploader */
            $fileUploader = $this->uploaderFactory->create(
                ['fileId' => $files['file']]
            );
            
            $fileUploader->setAllowedExtensions($this->allowedExtensions);
            
            $fileExtension = $fileUploader->getFileExtension();
            if (!$fileUploader->checkAllowedExtension($fileExtension)) {
                throw new LocalizedException(
                    __(
                        'Invalid file type. Only %1 files are allowed.',
                        implode(' and ', $this->allowedExtensions)
                    )
                );
            }

            $errorCount = $this->config->getErrorLimit();

            $delimiterConfig = $this->config->getDelimiter();
            $this->csvProcessor->setDelimiter($delimiterConfig);
            
            $enclosureConfig = $this->config->getEnclosure();
            $this->csvProcessor->setEnclosure($enclosureConfig);
            
            $this->csvProcessor->setStream(
                $files['file']['tmp_name']
            );

            $key = 0;
            $csvHeaders = [];
            while ($row = $this->csvProcessor->streamReadCsv()) {
                if ($key === 0) {
                    $csvHeaders = $row;
                } else {
                    try {
                        $row = array_combine($csvHeaders, $row);
                        $this->importer->import($row);
                        $imported++;
                    } catch (ImportException $e) {
                        $errorMsg = $e->getMessage();
                        if ($e->isImported()) {
                            $importError++;
                            $messages['error'][] = 'The row #' . $key . ': ' . $errorMsg;
                        } else {
                            $error++;
                            $messages['error'][] = 'The row #' . $key . ': ' . $errorMsg;
                        }
                    } catch (\Exception $e) {
                        $error++;
                        $errorMsg = $e->getMessage();
                        $messages['error'][] = 'The row #' . $key . ': ' . $errorMsg;
                    }

                    if ($errorCount <= ($error + $importError)) {
                        $messages['error'][] = '';
                        $messages['error'][] = sprintf(
                            'Import has been stopped at row #%d because the error limit of %d was reached',
                            $key,
                            $errorCount
                        );
                        break;
                    }
                }
                $key++;
            }

            $this->csvProcessor->unsetStream();

            if ($key < 1) {
                throw new LocalizedException(
                    __('Your file does not contain any orders.')
                );
                $importMessage = 'Your file does not contain any orders.';
                $importStatus = 1;
            }

            if ($imported) {
                $this->messageManager->addSuccessMessage(
                    __('%1 orders imported successfully', $imported)
                );
                $importMessage = __('%1 orders imported successfully', $imported);
                $importStatus = 0;
            }

            if ($importError) {
                $this->messageManager->addWarningMessage(
                    __(
                        '%1 orders were imported but may have experienced error(s) while creating invoices, shipments, or credit memos',
                        $importError
                    )
                );
                $importMessage = __(
                    'All orders have been imported successfully but %1 orders have experienced error(s) while creating their invoices, shipments, or credit memos',
                    $importError
                );
                $importStatus = 2;
            }

            if ($error) {
                $this->messageManager->addErrorMessage(
                    __('%1 orders were not imported due to error(s)', $error)
                );
                $importMessage = __('%1 orders were not imported due to error(s)', $error);
                $importStatus = 1;
            }

            if ($messages['success']) {
                foreach ($messages['success'] as $successMessage) {
                    $this->messageManager->addSuccessMessage($successMessage);
                }
            }
            
            if ($messages['error']) {
                foreach ($messages['error'] as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
            }

            $fileName = $this->uploadFileAndGetName(
                'import[file]',
                $this->logFile->getBaseImportedDir()
            );
            
            $relativePath = $this->logFile->getImportedRelativeFilePath($fileName);
            $importedFileSize = $this->importDownload->getReportSize($fileName);
            $importedFileSizeKb = round($importedFileSize / 1024, 2) . 'KB';
            $importedFileAndSize = $relativePath .' | ' . $importedFileSizeKb;
    
            // Log import history if enabled
            if ($this->systemConfig->isImportLogEnabled()) {
                $importlogModel->setFilename($fileName);
                $importlogModel->setFilenameFullpath($importedFileAndSize);
                $importlogModel->setUsername($currentAdminUsername);
                $importlogModel->setStatus($importStatus); // 0 = SUCCESS, 1 = FAILLED, 2 = WARNING
                $importlogModel->setType($importType);                  
                $importlogModel->setMessage($importMessage);
                $executionTime =  $this->getExecutionTime($importlogModel->getCreatedAt());
                $importlogModel->setExecutionTime($executionTime);
                $importlogModel->save();
            }
        } catch (LocalizedException $e) {
            $this->logger->critical(
                __($e->getMessage())
            );
            $this->messageManager->addErrorMessage(
                __($e->getMessage())
            );

            $this->importLogger->log(
                $this->timeZone->date(),
                $currentAdminUsername,
                $fileName,
                $executionTime,
                $importType,
                1,
                $e->getMessage()
            );
        }

        return $redirect;
    }

    /**
     * Calculate import time
     *
     * @param string $time
     * @return string
     */
    public function getExecutionTime($time)
    {
        $importLogTime = $this->timeZone->date($time);
        $timeDiff = $importLogTime->diff($this->timeZone->date());
        
        return $timeDiff->format('%H:%I:%S');
    }
    
    /**
     * Upload file and get name
     *
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return string
     */
    public function uploadFileAndGetName($input, $destinationFolder)
    {
        try {
            $files = $this->getRequest()->getFiles('import');

            /** @var FileUploader $uploader */
            $uploader = $this->uploaderFactory->create(
                ['fileId' => $files['file']]
            );
                $uploader->setAllowedExtensions($this->allowedExtensions);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                return $result['file'];
        } catch (\Exception $e) {
            if ($e->getCode() != FileUploader::TMP_NAME_EMPTY) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            }
        }
        return '';
    }
}
