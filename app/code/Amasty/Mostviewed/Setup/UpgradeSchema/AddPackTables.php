<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeSchema;

use Amasty\Mostviewed\Model\ResourceModel\Pack;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class AddPackTables
 * @package Amasty\Mostviewed\Setup\UpgradeSchema
 */
class AddPackTables
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createPackTables($setup);
    }

    /**
     * Create Pack Tables
     *
     * @param SchemaSetupInterface $installer
     */
    private function createPackTables($installer)
    {
        $mainTableName = $installer->getTable(Pack::PACK_TABLE);
        $tableName = $installer->getConnection()
            ->newTable($mainTableName)
            ->addColumn(
                'pack_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Pack ID'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Pack Status'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'store id'
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['default' => 1, 'nullable' => false],
                'Priority'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                [],
                'Pack Name'
            )
            ->addColumn(
                'customer_group_ids',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Customer groups'
            )
            ->addColumn(
                'product_ids',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Product Ids what to display'
            )
            ->addColumn(
                'block_title',
                Table::TYPE_TEXT,
                255,
                [],
                'Block Title'
            )
            ->addColumn(
                'discount_type',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Discount Type'
            )
            ->addColumn(
                'apply_for_parent',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Apply Discount for Main Product '
            )
            ->addColumn(
                'discount_amount',
                Table::TYPE_TEXT,
                255,
                [],
                'Discount Amount'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            );

        $installer->getConnection()->createTable($tableName);

        $tableName = $installer->getTable(Pack::PACK_PRODUCT_TABLE);
        $catalogTable = $installer->getTable('catalog_product_entity');
        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'pack_id',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'unsigned' => true, 'nullable' => false],
                'Pack id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'store id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'unsigned' => true, 'nullable' => false],
                'product id'
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['pack_id']),
                ['pack_id']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['product_id']),
                ['product_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    $tableName,
                    [
                        'entity_id',
                        'pack_id',
                        'store_id',
                        'product_id'
                    ],
                    true
                ),
                [
                    'entity_id',
                    'pack_id',
                    'store_id',
                    'product_id'
                ],
                ['type' => 'unique']
            )->addForeignKey(
                $installer->getFkName(
                    $tableName,
                    'pack_id',
                    $mainTableName,
                    'pack_id'
                ),
                'pack_id',
                $mainTableName,
                'pack_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    $tableName,
                    'product_id',
                    $catalogTable,
                    'entity_id'
                ),
                'product_id',
                $catalogTable,
                'entity_id',
                Table::ACTION_CASCADE
            )
        ;
        $installer->getConnection()->createTable($table);
    }
}
