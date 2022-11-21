<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Model\ResourceModel\Importlog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Ulmod\OrderImportExport\Model\Importlog',
            'Ulmod\OrderImportExport\Model\ResourceModel\Importlog'
        );
    }
    
    /**
     * Truncate
     *
     * @return void
     */
    public function truncate()
    {
        $this->getConnection()->truncateTable(
            $this->getMainTable()
        );
    }
}
