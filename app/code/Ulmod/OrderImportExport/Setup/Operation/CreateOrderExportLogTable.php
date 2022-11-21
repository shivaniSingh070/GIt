<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;

class CreateOrderExportLogTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $exportLogtable = $setup->getTable('ulmod_orderimportexport_export_log');

        if (!$setup->getConnection()->isTableExists($exportLogtable)) {
            return $exportLogtable = $setup->getConnection()
                ->newTable(
                    $exportLogtable
                )->addColumn(
                    'id',
                    DdlTable::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ]
                )->addColumn(
                    'created_at',
                    DdlTable::TYPE_DATETIME,
                    null,
                    ['nullable' => false]
                )->addColumn(
                    'filename',
                    DdlTable::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )->addColumn(
                    'filename_fullpath',
                    DdlTable::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )->addColumn(
                    'type',
                    DdlTable::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )->addColumn(
                    'username',
                    DdlTable::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )->addColumn(
                    'exported_file',
                    DdlTable::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )->addColumn(
                    'status',
                    DdlTable::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )->addColumn(
                    'execution_time',
                    DdlTable::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )->addColumn(
                    'message',
                    DdlTable::TYPE_TEXT,
                    '1M',
                    ['nullable' => true]
                )->setComment('Ulmod Order Export Log Table');
        }
    }
}
