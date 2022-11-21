<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\OptionSource;

/**
 * Class TopMenuLink
 * @package Amasty\Mostviewed\Model\OptionSource
 */
class TopMenuLink implements \Magento\Framework\Option\ArrayInterface
{
    const DISPLAY_FIRST = 1;

    const DISPLAY_LAST = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('No')],
            ['value' => self::DISPLAY_FIRST, 'label' => __('Display First')],
            ['value' => self::DISPLAY_LAST, 'label' => __('Display Last')]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            0                   => __('No'),
            self::DISPLAY_FIRST => __('Display First'),
            self::DISPLAY_LAST  => __('Display Last')
        ];
    }
}
