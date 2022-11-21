<?php
/**
 * @author: Noshad Ali
 * @package: Pixelmechanics_CatalogOrder
 * @date: 1Aug2019
 * trello: https://trello.com/c/UyuL2qfu/
 */

namespace Pixelmechanics\CatalogOrder\Block;

use Magento\Framework\View\Element\Template\Context;
use Pixelmechanics\CatalogOrder\Model\CatalogOrderFactory;
/**
 * CatalogOrder List block
 */
class CatalogOrderListData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CatalogOrder
     */
    protected $_catalogorder;
    public function __construct(
        Context $context,
        CatalogOrderFactory $catalogorder
    ) {
        $this->_catalogorder = $catalogorder;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Pixelmechanics CatalogOrder Module List Page'));
        
        if ($this->getCatalogOrderCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'pixelmechanics.catalogorder.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
                $this->getCatalogOrderCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCatalogOrderCollection()->load();
        }
        return parent::_prepareLayout();
    }

    public function getCatalogOrderCollection()
    {
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;

        $catalogorder = $this->_catalogorder->create();
        $collection = $catalogorder->getCollection();
        $collection->addFieldToFilter('status','1');
        //$catalogorder->setOrder('catalogorder_id','ASC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}