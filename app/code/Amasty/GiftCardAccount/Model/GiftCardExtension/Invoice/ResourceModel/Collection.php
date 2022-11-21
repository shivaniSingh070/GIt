<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Invoice::class,
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\ResourceModel\Invoice::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
