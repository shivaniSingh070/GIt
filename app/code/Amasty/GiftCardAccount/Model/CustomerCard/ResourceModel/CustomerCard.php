<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\CustomerCard\ResourceModel;

use Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerCard extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_customer_card';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, CustomerCardInterface::CUSTOMER_CARD_ID);
    }
}
