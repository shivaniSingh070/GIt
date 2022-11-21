<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Setup;

use Ulmod\OrderImportExport\Setup\Operation\CreateOrderImportLogTable;
use Ulmod\OrderImportExport\Setup\Operation\CreateOrderExportLogTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
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
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
       
        $this->createOrderImportLogTable->execute($setup);
        $this->createOrderExportLogTable->execute($setup);

        $setup->endSetup();
    }
}
