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
use Magento\Cms\Model\Template\FilterProvider;
/**
 * CatalogOrder View block
 */
class CatalogOrderView extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CatalogOrder
     */
    protected $_catalogorder;
    public function __construct(
        Context $context,
        CatalogOrderFactory $catalogorder,
        FilterProvider $filterProvider
    ) {
        $this->_catalogorder = $catalogorder;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Pixelmechanics CatalogOrder Module View Page'));
        
        return parent::_prepareLayout();
    }

    public function getSingleData()
    {
        $id = $this->getRequest()->getParam('id');
        $catalogorder = $this->_catalogorder->create();
        $singleData = $catalogorder->load($id);
        if($singleData->getCatalogOrderId() && $singleData->getStatus() == 1){
            return $singleData;
        }else{
            return false;
        }
    }
}