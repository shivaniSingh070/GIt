<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ulmod\OrderImportExport\Model\ResourceModel\Exportlog\CollectionFactory;

class Clearexportlog extends Action
{
    /**
     * @var CollectionFactory
     */
    protected $exportLogCollectionFactory;

    /**
     * @param Context $context
     * @param ExportLogResourceModel $exportLogCollectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $exportLogCollectionFactory
    ) {
        $this->exportLogCollectionFactory = $exportLogCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Ulmod_OrderImportExport::exportlog'
        );
    }

    /**
     * Clear export log action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $exportLogColFactory = $this->exportLogCollectionFactory->create();
            $exportLogColFactory->truncate();

            $this->messageManager->addSuccess(
                __('export log has been cleared.')
            );
            return $resultRedirect->setPath('*/*/exportlog');
        } catch (\Exception $e) {
            $this->messageManager->addError(
                $e->getMessage()
            );
            
            return $resultRedirect->setPath(
                '*/*/exportlog'
            );
        }
    }
}
