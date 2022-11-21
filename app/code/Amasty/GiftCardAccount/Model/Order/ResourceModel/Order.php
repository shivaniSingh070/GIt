<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Order\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

// Used to remove the restriction \Magento\Sales\Model\ResourceModel\Order\Plugin\Authorization::afterLoad()
class Order extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_order', 'entity_id');
    }
}
