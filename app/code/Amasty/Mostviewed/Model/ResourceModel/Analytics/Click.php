<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel\Analytics;

use Amasty\Mostviewed\Api\Data\ClickInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Click
 * @package Amasty\Mostviewed\Model\ResourceModel\Analytics
 */
class Click extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ClickInterface::MAIN_TABLE, ClickInterface::ID);
    }
}
