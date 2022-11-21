<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * @codeCoverageIgnore
 */
class Uninstall implements UninstallInterface
{
    const TABLE_NAMES = [
        \Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry::TABLE_NAME,
        \Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogDetail::TABLE_NAME,
        \Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession::TABLE_NAME,
        \Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogDetail::TABLE_NAME,
        \Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryEntry::TABLE_NAME,
        \Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryDetail::TABLE_NAME
    ];

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        foreach (self::TABLE_NAMES as $tableName) {
            $setup->getConnection()->dropTable($setup->getTable($tableName));
        }

        $setup->endSetup();
    }
}
