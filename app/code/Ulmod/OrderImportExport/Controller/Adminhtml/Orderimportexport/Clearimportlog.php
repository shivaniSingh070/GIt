<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ulmod\OrderImportExport\Model\ResourceModel\Importlog\CollectionFactory;

class Clearimportlog extends Action
{
    /**
     * @var CollectionFactory
     */
    protected $importLogCollectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $importLogCollectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $importLogCollectionFactory
    ) {
        $this->importLogCollectionFactory = $importLogCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ulmod_OrderImportExport::exportlog');
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
            $importLogColFactory = $this->importLogCollectionFactory->create();
            $importLogColFactory->truncate();

            $this->messageManager->addSuccess(
                __('import log has been cleared.')
            );
            return $resultRedirect->setPath('*/*/importlog');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/importlog');
        }
    }
}
