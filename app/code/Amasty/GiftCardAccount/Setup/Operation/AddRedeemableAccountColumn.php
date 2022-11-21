<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddRedeemableAccountColumn
{
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(Account::TABLE_NAME),
            GiftCardAccountInterface::IS_REDEEMABLE,
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Redeemable',
                'nullable' => true
            ]
        );
    }
}
