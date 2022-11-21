<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\LoginAttempt\LoginAttempt;
use Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel\LoginAttempt as LoginAttemptResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateLoginAttemptsTable
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
        $mainTable = $setup->getTable(LoginAttemptResource::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Admin Actions Log Login Attempts Table'
            )->addColumn(
                LoginAttempt::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Login Attempt ID'
            )->addColumn(
                LoginAttempt::DATE,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'default' => Table::TIMESTAMP_INIT,
                    'nullable' => false
                ],
                'Login Attempt Date Time'
            )->addColumn(
                LoginAttempt::USERNAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Login Attempt Username'
            )->addColumn(
                LoginAttempt::FULL_NAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Login Attempt User Full Name'
            )->addColumn(
                LoginAttempt::IP,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Login Attempt User IP'
            )->addColumn(
                LoginAttempt::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Login Attempt Status'
            )->addColumn(
                LoginAttempt::LOCATION,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Login Attempt Location'
            )->addColumn(
                LoginAttempt::COUNTRY_ID,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Login Attempt Country ID'
            )->addColumn(
                LoginAttempt::USER_AGENT,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Login Attempt User Agent'
            );
    }
}
