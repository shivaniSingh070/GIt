<?php

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\Store;

class CreateCookieGroupLinkStoreTable
{
    public const TABLE_NAME = 'amasty_gdprcookie_cookie_group_link_store';

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
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $relationStoreTable = $setup->getTable(self::TABLE_NAME);
        $cookieTable = $setup->getTable(CreateCookieTable::TABLE_NAME);
        $cookieGroupTable = $setup->getTable(CreateCookieGroupsTable::TABLE_NAME);
        $storeTable = $setup->getTable(Store::ENTITY);

        return $setup->getConnection()
            ->newTable(
                $relationStoreTable
            )->setComment(
                'Amasty GDPR Cookie Link Store Table'
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Id'
            )->addColumn(
                'cookie_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Cookie ID'
            )->addColumn(
                'group_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Cookie Group ID'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Store ID relation'
            )->addForeignKey(
                $setup->getFkName(
                    $relationStoreTable,
                    'cookie_id',
                    $cookieTable,
                    'id'
                ),
                'cookie_id',
                $cookieTable,
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $relationStoreTable,
                    'group_id',
                    $cookieGroupTable,
                    'id'
                ),
                'group_id',
                $cookieGroupTable,
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $relationStoreTable,
                    'store_id',
                    $storeTable,
                    Store::STORE_ID
                ),
                'store_id',
                $storeTable,
                Store::STORE_ID,
                Table::ACTION_CASCADE
            );
    }
}
