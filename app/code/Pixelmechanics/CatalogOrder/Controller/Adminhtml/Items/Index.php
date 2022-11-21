<?php
/**
 * @author: Noshad Ali
 * @package: Pixelmechanics_CatalogOrder
 * @date: 1Aug2019
 * trello: https://trello.com/c/UyuL2qfu/
 */

namespace Pixelmechanics\CatalogOrder\Controller\Adminhtml\Items;

class Index extends \Pixelmechanics\CatalogOrder\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Pixelmechanics_CatalogOrder::corder');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer List'));
        $resultPage->addBreadcrumb(__('Customer List'), __('Customer List'));
        $resultPage->addBreadcrumb(__('Items'), __('Items'));
        return $resultPage;
    }
}