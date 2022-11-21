<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession;
use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession as ActiveSessionResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateActiveSessionsTable
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
        $mainTable = $setup->getTable(ActiveSessionResource::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Admin Actions Log Active Sessions Table'
            )->addColumn(
                ActiveSession::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Active Session ID'
            )->addColumn(
                ActiveSession::SESSION_ID,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Active Session Session ID'
            )->addColumn(
                ActiveSession::USERNAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Active Session Username'
            )->addColumn(
                ActiveSession::FULL_NAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Active Session User Full Name'
            )->addColumn(
                ActiveSession::IP,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Active Session User IP'
            )->addColumn(
                ActiveSession::SESSION_START,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'default' => Table::TIMESTAMP_INIT,
                    'nullable' => false
                ],
                'Active Session Start Time'
            )->addColumn(
                ActiveSession::RECENT_ACTIVITY,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'default' => Table::TIMESTAMP_INIT_UPDATE,
                    'nullable' => false
                ],
                'Active Session Recent Activity Time'
            )->addColumn(
                ActiveSession::LOCATION,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Active Session Location'
            )->addColumn(
                ActiveSession::COUNTRY_ID,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Active Session Country ID'
            );
    }
}
