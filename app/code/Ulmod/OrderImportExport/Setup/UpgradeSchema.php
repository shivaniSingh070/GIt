<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Setup;

use Ulmod\OrderImportExport\Setup\Operation\CreateOrderImportLogTable;
use Ulmod\OrderImportExport\Setup\Operation\CreateOrderExportLogTable;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var CreateOrderImportLogTable
     */
    private $createOrderImportLogTable;

    /**
     * @var CreateOrderExportLogTable
     */
    private $createOrderExportLogTable;

    public function __construct(
        CreateOrderImportLogTable $createOrderImportLogTable,
        CreateOrderExportLogTable $createOrderExportLogTable
    ) {
        $this->createOrderImportLogTable = $createOrderImportLogTable;
        $this->createOrderExportLogTable = $createOrderExportLogTable;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->createOrderImportLogTable->execute($setup);
            $this->createOrderExportLogTable->execute($setup);
        }

        $setup->endSetup();
    }
}
