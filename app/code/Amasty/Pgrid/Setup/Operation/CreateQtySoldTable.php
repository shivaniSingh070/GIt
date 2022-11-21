<?php

namespace Amasty\Pgrid\Setup\Operation;

use Amasty\Pgrid\Api\Data\QtySoldInterface;
use Amasty\Pgrid\Model\Indexer\QtySoldProcessor;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateQtySoldTable
{
    const TABLE_NAME = 'amasty_pgrid_qty_sold';

    /**
     * @var QtySoldProcessor
     */
    private $processor;

    public function __construct(QtySoldProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
        $this->processor->markIndexerAsInvalid();
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Pgrid Qty Sold table'
            )->addColumn(
                QtySoldInterface::PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Product Id'
            )->addColumn(
                QtySoldInterface::QTY_SOLD,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Qty Sold'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    QtySoldInterface::PRODUCT_ID,
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                QtySoldInterface::PRODUCT_ID,
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
    }

    public function removeIndex(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $productTable = $setup->getTable('catalog_product_entity');
        $keyName = $setup->getFkName(
            $table,
            QtySoldInterface::PRODUCT_ID,
            $productTable,
            QtySoldInterface::PRODUCT_ID
        );

        $setup->getConnection()->dropForeignKey($table, $keyName);
    }
}
