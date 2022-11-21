<?php

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddPhoneAccountColumn
{
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(Account::TABLE_NAME),
            GiftCardAccountInterface::RECIPIENT_PHONE,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Recipient Phone',
                'nullable' => true
            ]
        );
    }
}
