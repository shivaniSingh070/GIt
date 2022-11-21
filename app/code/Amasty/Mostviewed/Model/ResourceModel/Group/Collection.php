<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel\Group;

use Amasty\Mostviewed\Model\ResourceModel\Group;
use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Amasty\Mostviewed\Model\ResourceModel\Group
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'group_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\Mostviewed\Model\Group::class, Group::class);
    }
}
