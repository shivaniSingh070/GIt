<?php

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;

class CreateCookieGroupsTable
{
    public const TABLE_NAME = 'amasty_gdprcookie_group';

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

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty GDPR Cookie Table with created cookie groups'
            )->addColumn(
                CookieGroupsInterface::ID,
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
                CookieGroupsInterface::NAME,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Cookie Name'
            )->addColumn(
                CookieGroupsInterface::DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Cookie Description'
            )->addColumn(
                CookieGroupsInterface::IS_ESSENTIAL,
                Table::TYPE_BOOLEAN,
                null,
                [],
                'Is Group Essential'
            )->addColumn(
                CookieGroupsInterface::IS_ENABLED,
                Table::TYPE_BOOLEAN,
                null,
                [],
                'Is Group Enabled'
            );
    }
}
