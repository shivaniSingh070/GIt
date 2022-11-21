<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo270
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $consentLogTable = $setup->getTable(CreateCookieConsentTable::TABLE_NAME);
        $customerTable = $setup->getTable('customer_entity');
        $fkName = $setup->getFkName(
            $consentLogTable,
            'customer_id',
            $customerTable,
            'entity_id'
        );
        //guests now can be logged. dropping FK with customer_entity
        $setup->getConnection()->dropForeignKey($consentLogTable, $fkName);
    }
}
