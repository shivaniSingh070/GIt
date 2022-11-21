<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Controller\Adminhtml\Orderimportexport;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Ulmod\OrderImportExport\Model\Config\Import as ConfigImport;
use Ulmod\OrderImportExport\Model\Config\Export as ConfigExport;

class Exportorder extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ulmod_OrderImportExport::export';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Export order
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->getRequest()->setParam('id', 0);
            $resultPage = $this->resultPageFactory->create();
            
            $resultPage->setActiveMenu(
                'Ulmod_OrderImportExport::export'
            );
            
            $resultPage->getConfig()->getTitle()
                ->prepend(__('Order Export'));
            
            $resultPage->addBreadcrumb(
                __('Order Export/Import'),
                __('Order Export')
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('Exception occurred during Order Export page load')
            );
            $resultRedirect->setPath('adminhtml/index');

            return $resultRedirect;
        }

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        $isAllowedImport = $this->_authorization->isAllowed(
            ConfigImport::ADMIN_RESOURCE
        );
        $isAllowedExport = $this->_authorization->isAllowed(
            ConfigExport::ADMIN_RESOURCE
        );

        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE)
            && ($isAllowedExport || $isAllowedImport);
    }
}
