<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ulmod\OrderImportExport\Model\ResourceModel\Exportlog\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ulmod\OrderImportExport\Model\ExportlogFactory;
use Ulmod\OrderImportExport\Model\Log\File as LogFile;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
        
class Exportdownload extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ExportlogFactory
     */
    protected $exportlogFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;
    
    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     * @param ExportlogFactory $exportlogFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        ExportlogFactory $exportlogFactory,
        Filesystem $filesystem
    ) {
        parent::__construct(
            $context
        );
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->exportlogFactory = $exportlogFactory;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Download exported file action
     *
     * @return void|\Magento\Backend\App\Action
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
       
        /** @var \Ulmod\OrderImportExport\Model\Exportlog $exportlogModel */
        $exportlogModel = $this->exportlogFactory->create()->load($data['id']);
                
        $fileName =  $exportlogModel->getFilename();
        
        if (!$this->exportFileExists($fileName)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/exportlog');
            return $resultRedirect;
        }
        
        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/csv',
            $this->getReportSize($fileName)
        );
        
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        
        $resultRaw->setContents($this->getReportOutput($fileName));
        
        return $resultRaw;
    }

    /**
     * Get exported file path
     *
     * @param string $filename
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getFilePath($filename)
    {
        if (preg_match('/\.\.(\\\|\/)/', $filename)) {
            throw new \InvalidArgumentException('Filename has not permitted symbols in it');
        }
        return $this->varDirectory->getRelativePath(LogFile::EXPORTED_HISTORY_DIR . $filename);
    }
    
    /**
     * Checks if exported file exists.
     *
     * @param string $filename
     * @return bool
     */
    public function exportFileExists($filename)
    {
        return $this->varDirectory->isFile($this->getFilePath($filename));
    }
    
    /**
     * Retrieve exported file size
     *
     * @param string $filename
     * @return int|mixed
     */
    public function getReportSize($filename)
    {
        return $this->varDirectory->stat($this->getFilePath($filename))['size'];
    }

    /**
     * Get exported file output
     *
     * @param string $filename
     * @return string
     */
    public function getReportOutput($filename)
    {
        return $this->varDirectory->readFile($this->getFilePath($filename));
    }
}
