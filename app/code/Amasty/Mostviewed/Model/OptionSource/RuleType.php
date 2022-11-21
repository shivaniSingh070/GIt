<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class RuleType
 * @package Amasty\Mostviewed\Model\OptionSource
 */
class RuleType implements OptionSourceInterface
{
    const PRODUCT = 'product';

    const CART = 'cart';

    const CATEGORY = 'category';

    const CUSTOM = 'custom';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRODUCT, 'label' => __('Product Page')],
            ['value' => self::CART, 'label' => __('Shopping Cart Page')],
            ['value' => self::CATEGORY, 'label' => __('Category Page')],
            ['value' => self::CUSTOM, 'label' => __('Custom')]
        ];
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function getNameByValue($value)
    {
        $result = '';
        foreach ($this->toOptionArray() as $item) {
            if ($item['value'] == $value) {
                $result = $item;
                break;
            }
        }

        return $result;
    }
}
