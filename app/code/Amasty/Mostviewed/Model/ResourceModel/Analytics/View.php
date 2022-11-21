<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel\Analytics;

use Amasty\Mostviewed\Api\Data\ViewInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class View
 * @package Amasty\Mostviewed\Model\ResourceModel\Analytics
 */
class View extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ViewInterface::MAIN_TABLE, ViewInterface::ID);
    }
}
