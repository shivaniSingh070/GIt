<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class BlockLayout
 * @package Amasty\Mostviewed\Model\OptionSource
 */
class BlockLayout implements OptionSourceInterface
{
    const SLIDER = 1;

    const GRID = 0;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::GRID, 'label' => __('Grid')],
            ['value' => self::SLIDER, 'label' => __('Slider')]
        ];
    }
}
