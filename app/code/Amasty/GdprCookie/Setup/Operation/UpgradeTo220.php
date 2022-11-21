<?php

declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Amasty\GdprCookie\Api\Data\CookieInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo220
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $cookieTable = $setup->getTable(CreateCookieTable::TABLE_NAME);
        $connection->addColumn(
            $cookieTable,
            CookieInterface::PROVIDER,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 127,
                'nullable' => false,
                'comment' => 'Cookie Provider',
            ]
        );
        $connection->addColumn(
            $cookieTable,
            CookieInterface::TYPE,
            [
                'type' => Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'Cookie Type',
            ]
        );

        $cookieStoreTable = $setup->getTable(CreateCookieStoreTable::TABLE_NAME);
        $connection->modifyColumn(
            $cookieStoreTable,
            'description',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'Cookie Description'
            ]
        );

        $connection->update(
            $cookieStoreTable,
            ['description' => null],
            ['description = ?' => '']
        );
    }
}
