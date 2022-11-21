<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Model;

use Magento\Framework\Model\AbstractModel;

class Importlog extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ulmod\OrderImportExport\Model\ResourceModel\Importlog');
    }
}
