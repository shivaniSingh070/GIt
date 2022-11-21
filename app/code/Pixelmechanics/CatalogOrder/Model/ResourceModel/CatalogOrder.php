<?php
/**
 * @author: Noshad Ali
 * @package: Pixelmechanics_CatalogOrder
 * @date: 1Aug2019
 * trello: https://trello.com/c/UyuL2qfu/
 */

namespace Pixelmechanics\CatalogOrder\Model\ResourceModel;

class CatalogOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('pixelmechanics_catalogorder', 'catalogorder_id');   //here "pixelmechanics_catalogorder" is table name and "catalogorder_id" is the primary key of custom table
    }
}