<?php
/**
 * @author: Noshad Ali
 * @package: Pixelmechanics_CatalogOrder
 * @date: 1Aug2019
 * trello: https://trello.com/c/UyuL2qfu/
 */

namespace Pixelmechanics\CatalogOrder\Model;

use Magento\Framework\Model\AbstractModel;

class CatalogOrder extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Pixelmechanics\CatalogOrder\Model\ResourceModel\CatalogOrder');
    }
}