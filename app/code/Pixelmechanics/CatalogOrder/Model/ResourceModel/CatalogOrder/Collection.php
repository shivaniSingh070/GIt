<?php
/**
 * @author: Noshad Ali
 * @package: Pixelmechanics_CatalogOrder
 * @date: 1Aug2019
 * trello: https://trello.com/c/UyuL2qfu/
 */

namespace Pixelmechanics\CatalogOrder\Model\ResourceModel\CatalogOrder;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'catalogorder_id';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Pixelmechanics\CatalogOrder\Model\CatalogOrder',
            'Pixelmechanics\CatalogOrder\Model\ResourceModel\CatalogOrder'
        );
    }
}