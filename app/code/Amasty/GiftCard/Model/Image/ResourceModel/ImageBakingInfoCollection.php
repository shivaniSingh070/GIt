<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ImageBakingInfoCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCard\Model\Image\ImageBakingInfo::class,
            \Amasty\GiftCard\Model\Image\ResourceModel\ImageBakingInfo::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
