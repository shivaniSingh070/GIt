<?php

declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo230
{
    public const OLD_COOKIE_STORE_TABLE_NAME = 'amasty_gdprcookie_cookie_store';

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $cookieStoreTable = $setup->getTable(self::OLD_COOKIE_STORE_TABLE_NAME);
        $cookieStoreDataTable = $setup->getTable(CreateCookieStoreTable::TABLE_NAME);

        if ($connection->isTableExists($cookieStoreTable)) {
            $connection->renameTable($cookieStoreTable, $cookieStoreDataTable);
        }

        $connection->addColumn(
            $cookieStoreDataTable,
            'cookie_lifetime',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'default'  => null,
                'comment'  => 'Cookie Lifetime'
            ]
        );
    }
}
