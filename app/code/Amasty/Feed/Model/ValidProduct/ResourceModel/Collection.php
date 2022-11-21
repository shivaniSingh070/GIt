<?php

namespace Amasty\Feed\Model\ValidProduct\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Amasty\Feed
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\Feed\Model\ValidProduct\ValidProduct::class,
            \Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct::class
        );
    }
}
