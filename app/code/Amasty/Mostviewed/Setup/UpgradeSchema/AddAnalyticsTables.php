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
 * Class AddAnalyticsTables
 * @package Amasty\Mostviewed\Setup\UpgradeSchema
 */
class AddAnalyticsTables
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createViewTempTable($setup);
        $this->createClickTempTable($setup);
        $this->createViewTable($setup);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createViewTempTable($installer)
    {
        $tableName = $installer->getConnection()
            ->newTable($installer->getTable('mostviewed_view_temp'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'View ID'
            )
            ->addColumn(
                'visitor_id',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Visitor Id'
            )
            ->addColumn(
                'block_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Block Id'
            );

        $installer->getConnection()->createTable($tableName);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createClickTempTable($installer)
    {
        $tableName = $installer->getConnection()
            ->newTable($installer->getTable('mostviewed_click_temp'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Click ID'
            )
            ->addColumn(
                'visitor_id',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Visitor Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'block_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Block Id'
            );

        $installer->getConnection()->createTable($tableName);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createViewTable($installer)
    {
        $tableName = $installer->getConnection()
            ->newTable($installer->getTable('mostviewed_analytics'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Analytic ID'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                15,
                [],
                'Type of Analytics'
            )
            ->addColumn(
                'counter',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Counter'
            )
            ->addColumn(
                'block_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Block Id'
            )->addColumn(
                'version_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Version Id'
            );

        $installer->getConnection()->createTable($tableName);
    }
}
