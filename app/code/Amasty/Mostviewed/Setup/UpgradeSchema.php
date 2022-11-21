<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Amasty\Mostviewed\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var UpgradeSchema\AddGroupTables
     */
    private $addGroupTables;

    /**
     * @var UpgradeSchema\UpdateIndexTable
     */
    private $updateIndexTable;

    /**
     * @var UpgradeSchema\AddPackTables
     */
    private $addPackTables;

    /**
     * @var UpgradeSchema\AddAnalyticsTables
     */
    private $addAnalyticsTables;

    /**
     * @var UpgradeSchema\AddNewPackColumns
     */
    private $addNewPackColumns;

    /**
     * @var UpgradeSchema\UpdateAnalyticsTables
     */
    private $updateAnalyticsTables;

    public function __construct(
        UpgradeSchema\AddGroupTables $addGroupTables,
        UpgradeSchema\UpdateIndexTable $updateIndexTable,
        UpgradeSchema\AddAnalyticsTables $addAnalyticsTables,
        UpgradeSchema\AddPackTables $addPackTables,
        UpgradeSchema\AddNewPackColumns $addNewPackColumns,
        UpgradeSchema\UpdateAnalyticsTables $updateAnalyticsTables
    ) {
        $this->addGroupTables = $addGroupTables;
        $this->updateIndexTable = $updateIndexTable;
        $this->addPackTables = $addPackTables;
        $this->addAnalyticsTables = $addAnalyticsTables;
        $this->addNewPackColumns = $addNewPackColumns;
        $this->updateAnalyticsTables = $updateAnalyticsTables;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @since 1.3.0 Product Conditions functional release */
        if (version_compare($context->getVersion(), '1.3', '<')) {
            $this->createProductIndexTable($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addGroupTables->execute($setup);
            $this->updateIndexTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addAnalyticsTables->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->addPackTables->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->addNewPackColumns->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.5.1', '<')) {
            $this->updateAnalyticsTables->execute($setup);
        }

        $setup->endSetup();
    }

    /**
     * Create Index Table
     *
     * @param SchemaSetupInterface $installer
     */
    private function createProductIndexTable(SchemaSetupInterface $installer)
    {
        $tableName = 'amasty_mostviewed_product_index';
        $table = $installer->getConnection()
            ->newTable($installer->getTable($tableName))
            ->addColumn(
                'index_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Index ID'
            )
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Related Product Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    $tableName,
                    [
                        'rule_id',
                        'product_id'
                    ],
                    true
                ),
                [
                    'rule_id',
                    'product_id'
                ]
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['rule_id']),
                ['rule_id']
            )
            ->addIndex(
                $installer->getIdxName($tableName, ['product_id']),
                ['product_id']
            )
            ->setComment('Product Matches');

        $installer->getConnection()->createTable($table);
    }
}
