<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\ResourceModel;

use Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class GiftCardPrice extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_price';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, GiftCardPriceInterface::PRICE_ID);
    }
}
