<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession as ActiveSessionResource;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry as LogEntryResource;
use Amasty\AdminActionsLog\Model\LoginAttempt\LoginAttempt;
use Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel\LoginAttempt as LoginAttemptResource;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryEntry as VisitHistoryEntryResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryEntry;

/**
 * @codeCoverageIgnore
 */
class UpdateSchemaTo200
{
    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $this->updateLogTable($setup);
        $this->updateActiveSessionsTable($setup);
        $this->updateLoginAttemptsTable($setup);
        $this->updateVisitTable($setup);
    }

    private function updateLoginAttemptsTable(SchemaSetupInterface $setup): void
    {
        $tableName = $setup->getTable(LoginAttemptResource::TABLE_NAME);
        $connection = $setup->getConnection();

        if ($connection->tableColumnExists($tableName, 'date_time')) {
            $connection->changeColumn(
                $tableName,
                'date_time',
                LoginAttempt::DATE,
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'comment' => 'Login Attempt Date Time',
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT
                ]
            );
        }

        if ($connection->tableColumnExists($tableName, 'name')) {
            $connection->changeColumn(
                $tableName,
                'name',
                LoginAttempt::FULL_NAME,
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Login Attempt User Full Name',
                    'nullable' => true,
                    'default' => null
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function updateActiveSessionsTable(SchemaSetupInterface $setup): void
    {
        $oldTableName = $setup->getTable('amasty_audit_active');
        $newTableName = $setup->getTable(ActiveSessionResource::TABLE_NAME);

        if (!$setup->tableExists($oldTableName)) {
            return;
        }

        $connection = $setup->getConnection();
        $connection->renameTable(
            $oldTableName,
            $newTableName
        );

        $connection->changeColumn(
            $newTableName,
            'date_time',
            ActiveSession::SESSION_START,
            [
                'type' => Table::TYPE_TIMESTAMP,
                'comment' => 'Active Session Start Time',
                'nullable' => false,
                'default' => Table::TIMESTAMP_INIT
            ]
        );
        $connection->changeColumn(
            $newTableName,
            ActiveSession::RECENT_ACTIVITY,
            ActiveSession::RECENT_ACTIVITY,
            [
                'type' => Table::TYPE_TIMESTAMP,
                'comment' => 'Active Session Recent Activity Time',
                'nullable' => false,
                'default' => Table::TIMESTAMP_INIT_UPDATE
            ]
        );
        $connection->changeColumn(
            $newTableName,
            'name',
            ActiveSession::FULL_NAME,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Active Session User Full Name',
                'nullable' => true,
                'default' => null
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function updateLogTable(SchemaSetupInterface $setup): void
    {
        $oldTableName = $setup->getTable('amasty_audit_log');
        $newTableName = $setup->getTable(LogEntryResource::TABLE_NAME);

        if (!$setup->tableExists($oldTableName)) {
            return;
        }

        $connection = $setup->getConnection();
        $connection->renameTable(
            $oldTableName,
            $newTableName
        );
        $connection->changeColumn(
            $newTableName,
            'date_time',
            LogEntry::DATE,
            [
                'type' => Table::TYPE_DATETIME,
                'comment' => 'Log Entry Date',
                'nullable' => true,
                'default' => null
            ]
        );
        $connection->changeColumn(
            $newTableName,
            'parametr_name',
            LogEntry::PARAMETER_NAME,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Log Entry Parameter Name',
                'nullable' => true,
                'default' => null
            ]
        );
        $connection->addColumn(
            $newTableName,
            LogEntry::IP,
            [
                'type' => Table::TYPE_TEXT,
                'default' => null,
                'nullable' => true,
                'comment' => 'Log Entry User Ip Address'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updateVisitTable(SchemaSetupInterface $setup): void
    {
        $oldTableName = $setup->getTable('amasty_audit_visit');
        $newTableName = $setup->getTable(VisitHistoryEntryResource::TABLE_NAME);

        if (!$setup->tableExists($oldTableName)) {
            return;
        }

        $connection = $setup->getConnection();
        $connection->renameTable(
            $oldTableName,
            $newTableName
        );

        $connection->changeColumn(
            $newTableName,
            'name',
            VisitHistoryEntry::FULL_NAME,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Visit History Entry Full Name',
                'nullable' => true,
                'default' => null
            ]
        );
    }
}
