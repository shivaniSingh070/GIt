<?php

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Amasty\GdprCookie\Api\Data\CookieInterface;

class CreateCookieTable
{
    public const TABLE_NAME = 'amasty_gdprcookie_cookie';

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
                'Amasty GDPR Cookie Table with created cookies'
            )->addColumn(
                CookieInterface::ID,
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
                CookieInterface::NAME,
                Table::TYPE_TEXT,
                225,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Cookie Name'
            )->addColumn(
                CookieInterface::DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Cookie Description'
            )->addIndex(
                $setup->getIdxName(
                    self::TABLE_NAME,
                    [CookieInterface::NAME],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [CookieInterface::NAME],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
    }
}
