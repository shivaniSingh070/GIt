<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeSchema;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class AddNewPackColumns
 * @package Amasty\Mostviewed\Setup\UpgradeSchema
 */
class AddNewPackColumns
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(Pack::PACK_TABLE);
        $setup->getConnection()->addColumn(
            $table,
            PackInterface::CART_MESSAGE,
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => false,
                'size'     => 12,
                'comment'  => 'Cart Message'
            ]
        );

        $setup->getConnection()->addColumn(
            $table,
            PackInterface::DATE_FROM,
            [
                'type'     => Table::TYPE_DATETIME,
                'nullable' => true,
                'comment'  => 'From'
            ]
        );

        $setup->getConnection()->addColumn(
            $table,
            PackInterface::DATE_TO,
            [
                'type'     => Table::TYPE_DATETIME,
                'nullable' => true,
                'comment'  => 'To'
            ]
        );
    }
}
