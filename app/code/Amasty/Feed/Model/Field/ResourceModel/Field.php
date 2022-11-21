<?php

namespace Amasty\Feed\Model\Field\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Class Field Resource Model
 *
 * @package Amasty\Feed
 */
class Field extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_feed_field', 'feed_field_id');
    }
}
