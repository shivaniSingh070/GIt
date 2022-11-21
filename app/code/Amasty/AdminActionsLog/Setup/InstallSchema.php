<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateLogEntryTable
     */
    private $createLogEntryTable;

    /**
     * @var Operation\CreateLogDetailsTable
     */
    private $createLogDetailsTable;

    /**
     * @var Operation\CreateActiveSessionsTable
     */
    private $createActiveSessionsTable;

    /**
     * @var Operation\CreateVisitHistoryEntryTable
     */
    private $createVisitHistoryEntryTable;

    /**
     * @var Operation\CreateVisitHistoryDetailsTable
     */
    private $createVisitHistoryDetailsTable;

    /**
     * @var Operation\CreateLoginAttemptsTable
     */
    private $createLoginAttemptsTable;

    public function __construct(
        Operation\CreateLogEntryTable $createLogEntryTable,
        Operation\CreateLogDetailsTable $createLogDetailsTable,
        Operation\CreateActiveSessionsTable $createActiveSessionsTable,
        Operation\CreateVisitHistoryEntryTable $createVisitHistoryEntryTable,
        Operation\CreateVisitHistoryDetailsTable $createVisitHistoryDetailsTable,
        Operation\CreateLoginAttemptsTable $createLoginAttemptsTable
    ) {
        $this->createLogEntryTable = $createLogEntryTable;
        $this->createLogDetailsTable = $createLogDetailsTable;
        $this->createActiveSessionsTable = $createActiveSessionsTable;
        $this->createVisitHistoryEntryTable = $createVisitHistoryEntryTable;
        $this->createVisitHistoryDetailsTable = $createVisitHistoryDetailsTable;
        $this->createLoginAttemptsTable = $createLoginAttemptsTable;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $this->createLogEntryTable->execute($setup);
        $this->createLogDetailsTable->execute($setup);
        $this->createActiveSessionsTable->execute($setup);
        $this->createVisitHistoryEntryTable->execute($setup);
        $this->createVisitHistoryDetailsTable->execute($setup);
        $this->createLoginAttemptsTable->execute($setup);

        $setup->endSetup();
    }
}
