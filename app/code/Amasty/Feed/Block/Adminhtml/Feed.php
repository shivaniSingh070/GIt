<?php
namespace Amasty\Feed\Block\Adminhtml;

/**
 * Class Feed
 *
 * @package Amasty\Feed
 */
class Feed extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Amasty_Feed';
        $this->_headerText = __('Feeds');
        $this->_addButtonLabel = __('Add New Feed');
        parent::_construct();
    }
}
