<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry as LogEntryResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateLogEntryTable
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
        $mainTable = $setup->getTable(LogEntryResource::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Admin Actions Log Entry Table'
            )->addColumn(
                LogEntry::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Log Entry ID'
            )->addColumn(
                LogEntry::DATE,
                Table::TYPE_DATETIME,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Log Entry Date'
            )->addColumn(
                LogEntry::USERNAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Log Entry Username'
            )->addColumn(
                LogEntry::TYPE,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Log Entry Type'
            )->addColumn(
                LogEntry::CATEGORY,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Log Entry Category'
            )->addColumn(
                LogEntry::CATEGORY_NAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Log Entry Category Name'
            )->addColumn(
                LogEntry::PARAMETER_NAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Log Entry Parameter Name'
            )->addColumn(
                LogEntry::ELEMENT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Log Entry Element ID'
            )->addColumn(
                LogEntry::ITEM,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Log Entry Item'
            )->addColumn(
                LogEntry::IP,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Log Entry Item'
            )->addColumn(
                LogEntry::STORE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => null
                ],
                'Log Entry Store ID'
            );
    }
}
