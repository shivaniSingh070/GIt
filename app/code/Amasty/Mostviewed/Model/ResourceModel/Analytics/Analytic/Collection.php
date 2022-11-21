<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Model\Analytics\Analytic;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic as AnalyticResource;

/**
 * Class Collection
 * @package Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = AnalyticInterface::ID;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Analytic::class, AnalyticResource::class);
    }
}
