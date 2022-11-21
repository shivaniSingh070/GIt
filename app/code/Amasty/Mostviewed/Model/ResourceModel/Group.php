<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel;

use Magento\Rule\Model\ResourceModel\AbstractResource;

/**
 * Class Group
 * @package Amasty\Mostviewed\Model\ResourceModel
 */
class Group extends AbstractResource
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_mostviewed_group', 'group_id');
    }
}
