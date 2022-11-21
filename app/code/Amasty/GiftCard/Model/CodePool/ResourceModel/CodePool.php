<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool\ResourceModel;

use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CodePool extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_code_pool';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, CodePoolInterface::CODE_POOL_ID);
    }
}
