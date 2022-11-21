<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PackPosition
 * @package Amasty\Mostviewed\Model\OptionSource
 */
class PackPosition implements OptionSourceInterface
{
    const PRODUCT_INFO = 'below';

    const TAB = 'tab';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRODUCT_INFO, 'label' => __('Below Product Info')],
            ['value' => self::TAB, 'label' => __('Product Tab')]
        ];
    }
}
