<?php
/**
 * @author: Noshad Ali
 * @package: Pixelmechanics_CatalogOrder
 * @date: 1Aug2019
 * trello: https://trello.com/c/UyuL2qfu/
 */

namespace Pixelmechanics\CatalogOrder\Block\Adminhtml\Items\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pixelmechanics_catalogorder_items_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Item'));
    }
}
