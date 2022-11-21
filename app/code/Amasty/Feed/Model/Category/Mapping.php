<?php

namespace Amasty\Feed\Model\Category;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Mapping Model
 *
 * @package Amasty\Feed
 */
class Mapping extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Amasty\Feed\Model\Category\ResourceModel\Mapping::class);
        $this->setIdFieldName('entity_id');
    }
}
