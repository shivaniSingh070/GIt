<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryEntry;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryEntry as VisitHistoryEntryResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateVisitHistoryEntryTable
{
    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
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
    private function createTable(SchemaSetupInterface $setup): Table
    {
        $mainTable = $setup->getTable(VisitHistoryEntryResource::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Admin Actions Visit History Entry Table'
            )->addColumn(
                VisitHistoryEntry::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Visit History Entry ID'
            )->addColumn(
                VisitHistoryEntry::USERNAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Visit History Entry Username'
            )->addColumn(
                VisitHistoryEntry::USERNAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Visit History Entry Username'
            )->addColumn(
                VisitHistoryEntry::FULL_NAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Visit History Entry Full Name'
            )->addColumn(
                VisitHistoryEntry::SESSION_START,
                Table::TYPE_DATETIME,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Visit History Entry Session Start'
            )->addColumn(
                VisitHistoryEntry::SESSION_END,
                Table::TYPE_DATETIME,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Visit History Entry Session End'
            )->addColumn(
                VisitHistoryEntry::IP,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Visit History Entry IP'
            )->addColumn(
                VisitHistoryEntry::LOCATION,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Visit History Entry Location'
            )->addColumn(
                VisitHistoryEntry::SESSION_ID,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Visit History Entry Session Id'
            );
    }
}
