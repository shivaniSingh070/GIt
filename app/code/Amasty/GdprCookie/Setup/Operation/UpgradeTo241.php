<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo241
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->processTableGroup($setup);
        $this->processTableGroupStoreData($setup);
    }

    private function processTableGroup(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $groupTable = $setup->getTable(CreateCookieGroupsTable::TABLE_NAME);
        $connection->addColumn(
            $groupTable,
            CookieGroupsInterface::SORT_ORDER,
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Sort Order'
            ]
        );
    }

    private function processTableGroupStoreData(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $groupStoreDataTable = $setup->getTable(CreateCookieGroupStoreTable::TABLE_NAME);
        $connection->addColumn(
            $groupStoreDataTable,
            'sort_order',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'Sort Order'
            ]
        );
    }
}
