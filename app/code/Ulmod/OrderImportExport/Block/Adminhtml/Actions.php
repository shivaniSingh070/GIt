<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Block\Adminhtml;

use Magento\Backend\Block\Widget\Form\Container;

class Actions extends Container
{
    /**
     * Init container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_mode       = 'actions';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Ulmod_OrderImportExport';
        parent::_construct();
        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');
    }
}
