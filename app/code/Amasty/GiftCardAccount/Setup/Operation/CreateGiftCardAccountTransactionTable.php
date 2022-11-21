<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\AccountTransaction;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateGiftCardAccountTransactionTable
{
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
    private function createTable(SchemaSetupInterface $setup): Table
    {
        $mainTable = $setup->getTable(AccountTransaction::TABLE_NAME);
        $accountTable = $setup->getTable(Account::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Gift Card Account Transaction Table'
            )->addColumn(
                'transaction_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Account Transaction ID'
            )->addColumn(
                'account_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Account ID'
            )->addColumn(
                'started_in',
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => true,
                    'default'  => null
                ],
                'Started In'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    'account_id',
                    $accountTable,
                    GiftCardAccountInterface::ACCOUNT_ID
                ),
                'account_id',
                $accountTable,
                GiftCardAccountInterface::ACCOUNT_ID,
                Table::ACTION_CASCADE
            )->addIndex(
                $setup->getIdxName(
                    $mainTable,
                    ['account_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['account_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
    }
}
