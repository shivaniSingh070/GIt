<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Amasty\GiftCard\Model\Config\Source\Usage;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddUsageAccountColumn
{
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(Account::TABLE_NAME),
            GiftCardAccountInterface::USAGE,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Usage',
                'length' => 15,
                'default' => Usage::MULTIPLE,
                'nullable' => false
            ]
        );
    }
}
