<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeSchema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;

/**
 * Class UpdateAnalyticsTables
 * @package Amasty\Mostviewed\Setup\UpgradeSchema
 */
class UpdateAnalyticsTables
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->updateFields($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function updateFields($setup)
    {
        $connection = $setup->getConnection();
        $table = $setup->getTable(AnalyticInterface::MAIN_TABLE);

        if ($connection->tableColumnExists($table, AnalyticInterface::COUNTER)) {
            $connection->modifyColumn(
                $table,
                AnalyticInterface::COUNTER,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length'   => '9,2'
                ]
            );

        }
    }
}
