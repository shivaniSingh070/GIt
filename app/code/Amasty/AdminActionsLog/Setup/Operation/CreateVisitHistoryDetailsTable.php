<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryDetail as VisitHistoryDetailResource;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryEntry as VisitHistoryEntryResource;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryEntry;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateVisitHistoryDetailsTable
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
        $mainTable = $setup->getTable(VisitHistoryDetailResource::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Admin Actions Log Visit History Detail Table'
            )->addColumn(
                VisitHistoryDetail::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Visit History Detail ID'
            )->addColumn(
                VisitHistoryDetail::VISIT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Visit History Entry ID'
            )->addColumn(
                VisitHistoryDetail::PAGE_NAME,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Page Name'
            )->addColumn(
                VisitHistoryDetail::PAGE_URL,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Page URL'
            )->addColumn(
                VisitHistoryDetail::STAY_DURATION,
                Table::TYPE_INTEGER,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Stay Duration'
            )->addColumn(
                VisitHistoryDetail::SESSION_ID,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Session ID'
            )->addIndex(
                $setup->getIdxName(VisitHistoryDetailResource::TABLE_NAME, VisitHistoryDetail::VISIT_ID),
                VisitHistoryDetail::VISIT_ID
            )
            ->addForeignKey(
                $setup->getFkName(
                    VisitHistoryDetailResource::TABLE_NAME,
                    VisitHistoryDetail::VISIT_ID,
                    VisitHistoryEntryResource::TABLE_NAME,
                    VisitHistoryEntry::ID
                ),
                VisitHistoryDetail::VISIT_ID,
                $setup->getTable(VisitHistoryEntryResource::TABLE_NAME),
                VisitHistoryEntry::ID,
                Table::ACTION_CASCADE
            );
    }
}
