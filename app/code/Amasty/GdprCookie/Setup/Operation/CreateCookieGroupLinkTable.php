<?php

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateCookieGroupLinkTable
{
    public const TABLE_NAME = 'amasty_gdprcookie_cookie_group_link';

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
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
        $table = $setup->getTable(self::TABLE_NAME);
        $cookieTable = $setup->getTable(CreateCookieTable::TABLE_NAME);
        $cookieGroupTable = $setup->getTable(CreateCookieGroupsTable::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty GDPR Cookie Link Table'
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
                    'nullable' => false
                ],
                'Cookie Group ID'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
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
                    $table,
                    'group_id',
                    $cookieGroupTable,
                    'id'
                ),
                'group_id',
                $cookieGroupTable,
                'id',
                Table::ACTION_CASCADE
            );
    }
}
