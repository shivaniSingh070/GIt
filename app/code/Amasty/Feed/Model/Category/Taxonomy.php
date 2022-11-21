<?php

namespace Amasty\Feed\Model\Category;

use Amasty\Feed\Model\Category\ResourceModel\Taxonomy as ResourceTaxonomy;

/**
 * Class Taxonomy
 *
 * @package Amasty\Feed
 */
class Taxonomy extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceTaxonomy::class);
    }
}
