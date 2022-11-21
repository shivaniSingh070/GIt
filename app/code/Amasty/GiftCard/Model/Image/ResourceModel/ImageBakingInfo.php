<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image\ResourceModel;

use Amasty\GiftCard\Api\Data\ImageBakingInfoInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class ImageBakingInfo extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_image_baking_info';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ImageBakingInfoInterface::INFO_ID);
    }
}
