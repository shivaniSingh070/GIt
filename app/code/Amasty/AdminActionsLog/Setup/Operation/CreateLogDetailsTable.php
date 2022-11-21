<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\LogEntry\LogDetail;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogDetail as LogDetailResource;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry as LogEntryResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateLogDetailsTable
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
        $mainTable = $setup->getTable(LogDetailResource::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Admin Actions Log Log Detail Table'
            )->addColumn(
                LogDetail::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Log Detail ID'
            )->addColumn(
                LogDetail::LOG_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Log Entry ID'
            )->addColumn(
                LogDetail::NAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Name'
            )->addColumn(
                LogDetail::OLD_VALUE,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Old Value'
            )->addColumn(
                LogDetail::NEW_VALUE,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'New Value'
            )->addColumn(
                LogDetail::MODEL,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Model'
            )->addIndex(
                $setup->getIdxName(LogDetailResource::TABLE_NAME, LogDetail::LOG_ID),
                LogDetail::LOG_ID
            )
            ->addForeignKey(
                $setup->getFkName(
                    LogDetailResource::TABLE_NAME,
                    LogDetail::LOG_ID,
                    LogEntryResource::TABLE_NAME,
                    LogEntry::ID
                ),
                LogDetail::LOG_ID,
                $setup->getTable(LogEntryResource::TABLE_NAME),
                LogEntry::ID,
                Table::ACTION_CASCADE
            );
    }
}
