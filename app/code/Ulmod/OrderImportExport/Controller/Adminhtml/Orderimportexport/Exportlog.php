<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Exportlog extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
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
     * Export log action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        
        $resultPage->setActiveMenu(
            'Ulmod_OrderImportExport::exportlog'
        );
        
        $resultPage->addBreadcrumb(
            __('Order Export History'),
            __('Order Export History')
        );
        $resultPage->addBreadcrumb(
            __('Order Export History'),
            __('Order Export History')
        );
        
        $resultPage->getConfig()->getTitle()
            ->prepend(__('Order Export History'));

        return $resultPage;
    }
}
