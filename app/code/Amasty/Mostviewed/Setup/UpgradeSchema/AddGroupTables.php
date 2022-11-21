<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeSchema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class AddGroupTables
 * @package Amasty\Mostviewed\Setup\UpgradeSchema
 */
class AddGroupTables
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createRuleTable($setup);
    }

    /**
     * Create Rule Table
     *
     * @param SchemaSetupInterface $installer
     */
    private function createRuleTable($installer)
    {
        $tableName = $installer->getConnection()
            ->newTable($installer->getTable('amasty_mostviewed_group'))
            ->addColumn(
                'group_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Group ID'
            )
            ->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Group Status'
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
                'Group Name'
            )
            ->addColumn(
                'block_position',
                Table::TYPE_TEXT,
                255,
                [],
                'Block Position'
            )
            ->addColumn(
                'stores',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Stores'
            )
            ->addColumn(
                'customer_group_ids',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Customer groups'
            )
            ->addColumn(
                'where_conditions_serialized',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Where to display Products Serialized'
            )
            ->addColumn(
                'category_ids',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Category Ids where to display'
            )
            ->addColumn(
                'conditions_serialized',
                Table::TYPE_TEXT,
                '2M',
                [],
                'What to display Products Serialized'
            )
            ->addColumn(
                'same_as_conditions_serialized',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Same as Serialized'
            )
            ->addColumn(
                'block_title',
                Table::TYPE_TEXT,
                255,
                [],
                'Block Title'
            )
            ->addColumn(
                'block_layout',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Block Layout'
            )
            ->addColumn(
                'source_type',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Source Type'
            )
            ->addColumn(
                'same_as',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Same as attribute'
            )
            ->addColumn(
                'replace_type',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Replace Types'
            )
            ->addColumn(
                'add_to_cart',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Display Add to cart button'
            )
            ->addColumn(
                'max_products',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Max Products'
            )
            ->addColumn(
                'sorting',
                Table::TYPE_TEXT,
                255,
                [],
                'Sort Products By'
            )
            ->addColumn(
                'for_out_of_stock',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Show For Out of Stock only'
            )->addColumn(
                'show_out_of_stock',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false],
                'Show Out of Stock'
            )->addColumn(
                'layout_update_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Layout Update Id'
            );

        $installer->getConnection()->createTable($tableName);
    }
}
