<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Sortby
 * @package Amasty\Mostviewed\Model\OptionSource
 */
class Sortby implements OptionSourceInterface
{
    const RANDOM = 'random';

    const NAME = 'name';

    const PRICE_ASC = 'price_asc';

    const PRICE_DESC = 'price_desc';

    const NEWEST = 'newest';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::RANDOM, 'label' => __('Random')],
            ['value' => self::NAME, 'label' => __('Name')],
            ['value' => self::PRICE_DESC, 'label' => __('Price: high to low')],
            ['value' => self::PRICE_ASC, 'label' => __('Price: low to high')],
            ['value' => self::NEWEST, 'label' => __('Newest')]
        ];
    }
}
