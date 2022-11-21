<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Amasty\GdprCookie\Api\Data\CookieInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo240
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->processTableCookie($setup);
        $this->processTableGroup($setup);
        $this->processTableCookieStoreData($setup);
        $this->processTableGroupStoreData($setup);
    }

    private function processTableCookie(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $cookieTable = $setup->getTable(CreateCookieTable::TABLE_NAME);
        $groupTable = $setup->getTable(CreateCookieGroupsTable::TABLE_NAME);
        $connection->changeColumn(
            $cookieTable,
            'cookie_lifetime',
            CookieInterface::LIFETIME,
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'default'  => null,
                'comment'  => 'Cookie Lifetime'
            ]
        );
        $connection->addColumn(
            $cookieTable,
            'group_id',
            [
                'unsigned' => true,
                'nullable' => true,
                'type'     => Table::TYPE_INTEGER,
                'comment'  => 'Group Id'
            ]
        );
        $connection->addForeignKey(
            $setup->getFkName(
                $cookieTable,
                'group_id',
                $groupTable,
                'id'
            ),
            $cookieTable,
            'group_id',
            $groupTable,
            'id',
            Table::ACTION_SET_NULL
        );
    }

    private function processTableGroup(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $groupCookieTable = $setup->getTable('amasty_gdprcookie_group_cookie');
        $groupTable = $setup->getTable(CreateCookieGroupsTable::TABLE_NAME);
        if ($connection->isTableExists($groupCookieTable)) {
            $connection->renameTable($groupCookieTable, $groupTable);
        }
    }

    private function processTableCookieStoreData(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $cookieStoreTable = $setup->getTable('amasty_gdprcookie_cookie_store');
        $cookieStoreDataTable = $setup->getTable(CreateCookieStoreTable::TABLE_NAME);
        $groupTable = $setup->getTable(CreateCookieGroupsTable::TABLE_NAME);
        if ($connection->isTableExists($cookieStoreTable)) {
            $connection->renameTable($cookieStoreTable, $cookieStoreDataTable);
        }
        $connection->changeColumn(
            $cookieStoreDataTable,
            'cookie_lifetime',
            CookieInterface::LIFETIME,
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'default'  => null,
                'comment'  => 'Cookie Lifetime'
            ]
        );
        $connection->addColumn(
            $cookieStoreDataTable,
            'group_id',
            [
                'unsigned' => true,
                'nullable' => true,
                'type'     => Table::TYPE_INTEGER,
                'comment'  => 'Group Id'
            ]
        );
        $connection->addForeignKey(
            $setup->getFkName(
                $cookieStoreDataTable,
                'group_id',
                $groupTable,
                'id'
            ),
            $cookieStoreDataTable,
            'group_id',
            $groupTable,
            'id',
            Table::ACTION_SET_NULL
        );
        $connection->addIndex(
            $cookieStoreDataTable,
            $setup->getIdxName($cookieStoreDataTable, ['cookie_id', 'store_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
            ['cookie_id', 'store_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    private function processTableGroupStoreData(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $cookieGroupStoreTable = $setup->getTable('amasty_gdprcookie_cookie_group_store');
        $groupStoreDataTable = $setup->getTable(CreateCookieGroupStoreTable::TABLE_NAME);
        if ($connection->isTableExists($cookieGroupStoreTable)) {
            $connection->renameTable($cookieGroupStoreTable, $groupStoreDataTable);
        }
        $connection->modifyColumn(
            $groupStoreDataTable,
            'name',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'Cookie Group Name'
            ]
        );
        $connection->modifyColumn(
            $groupStoreDataTable,
            'description',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'Cookie Group Description'
            ]
        );
    }
}
