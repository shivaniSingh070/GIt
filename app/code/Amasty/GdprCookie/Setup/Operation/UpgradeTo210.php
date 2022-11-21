<?php

declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo210
{
    /**
     * @var CreateCookieGroupLinkStoreTable
     */
    private $createCookieGroupLinkStoreTable;

    public function __construct(CreateCookieGroupLinkStoreTable $createCookieGroupLinkStoreTable)
    {
        $this->createCookieGroupLinkStoreTable = $createCookieGroupLinkStoreTable;
    }

    public function execute(SchemaSetupInterface $setup)
    {
        $this->createCookieGroupLinkStoreTable->execute($setup);
        $this->renameStoreTables($setup);
        $this->addIsEnabledColumn($setup);
    }

    private function renameStoreTables(SchemaSetupInterface $setup)
    {
        $tablesToRenameMap = [
            [
                $setup->getTable('amasty_gdprcookie_cookie_description'),
                $setup->getTable(CreateCookieStoreTable::TABLE_NAME)
            ],
            [
                $setup->getTable('amasty_gdprcookie_cookie_group_description'),
                $setup->getTable(CreateCookieGroupStoreTable::TABLE_NAME)
            ]
        ];

        foreach ($tablesToRenameMap as $tablesToRename) {
            if (!$setup->tableExists($tablesToRename[0])) {
                continue;
            }

            $setup->getConnection()->renameTable($tablesToRename[0], $tablesToRename[1]);
        }
    }

    private function addIsEnabledColumn(SchemaSetupInterface $setup)
    {
        $tablesToAddColumn = [
            CreateCookieStoreTable::TABLE_NAME,
            CreateCookieGroupStoreTable::TABLE_NAME,
            CreateCookieTable::TABLE_NAME
        ];

        foreach ($tablesToAddColumn as $tableName) {
            $setup->getConnection()->addColumn(
                $setup->getTable($tableName),
                'is_enabled',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default' => 1,
                    'comment' => 'Is Enabled'
                ]
            );
        }
    }
}
