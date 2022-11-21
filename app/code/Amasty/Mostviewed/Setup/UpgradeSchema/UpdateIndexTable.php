<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeSchema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpdateIndexTable
 * @package Amasty\Mostviewed\Setup\UpgradeSchema
 */
class UpdateIndexTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->addFields($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addFields($setup)
    {
        $table = $setup->getTable(RuleIndex::MAIN_TABLE);
        $setup->getConnection()->addColumn(
            $table,
            RuleIndex::RELATION,
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => false,
                'size'     => 12,
                'comment'  => 'Type of rule'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            RuleIndex::STORE_ID,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => false,
                'default'  => 0,
                'size'     => 10,
                'comment'  => 'Store id when rule applicable'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            RuleIndex::POSITION,
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => false,
                'comment'  => 'Position where label need displayed'
            ]
        );

        if ($setup->getConnection()->tableColumnExists($table, 'product_id')) {
            $setup->getConnection()->changeColumn(
                $table,
                'product_id',
                'entity_id',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'Must be Product Or Category Id'
                ]
            );
        }
    }
}
