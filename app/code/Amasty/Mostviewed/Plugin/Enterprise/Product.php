<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Plugin\Enterprise;

use Amasty\Mostviewed\Plugin\Community\AbstractProduct;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;

/**
 * Class Product
 * @package Amasty\Mostviewed\Plugin\Enterprise
 */
class Product extends AbstractProduct
{
    const UP_SELL_TYPE_NAME = 'upsell-rule';

    const RELATED_TYPE_NAME = 'related-rule';

    const CROSSSELL_TYPE_NAME = 'crosssell-rule';

    /**
     * @param $subject
     * @param array|\Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterGetItemCollection($subject, $collection)
    {
        switch ($subject->getData('type')) {
            case self::RELATED_TYPE_NAME:
                $type = BlockPosition::PRODUCT_INTO_RELATED;
                break;
            case self::UP_SELL_TYPE_NAME:
                $type = BlockPosition::PRODUCT_INTO_UPSELL;
                break;
            case self::CROSSSELL_TYPE_NAME:
                $type = BlockPosition::CART_INTO_CROSSSEL;
                break;
            default:
                $type = '';
        }

        return $this->prepareCollection($type, $collection, $subject);
    }
}
