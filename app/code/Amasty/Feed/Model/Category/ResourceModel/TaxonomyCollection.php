<?php

namespace Amasty\Feed\Model\Category\ResourceModel;

/**
 * Class TaxonomyCollection
 *
 * @package Amasty\Feed
 */
class TaxonomyCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Feed\Model\Category\Taxonomy::class,
            \Amasty\Feed\Model\Category\ResourceModel\Taxonomy::class
        );
    }
}
