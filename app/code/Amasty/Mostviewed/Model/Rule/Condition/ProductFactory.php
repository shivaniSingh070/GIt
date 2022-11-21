<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Rule\Condition;

/**
 * Class ProductFactory
 * @package Amasty\Mostviewed\Model\Rule\Condition
 * @codingStandardsIgnoreFile
 */
class ProductFactory extends \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Amasty\Mostviewed\Model\Rule\Condition\Product::class
    ) {
        parent::__construct($objectManager, $instanceName);
    }
}
