<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ReplaceType
 * @package Amasty\Mostviewed\Model\OptionSource
 */
class ReplaceType implements OptionSourceInterface
{
    const REPLACE = '0';

    const ADD = '1';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::REPLACE, 'label' => __('Replace Manually Added Products')],
            ['value' => self::ADD, 'label' => __('Append to Manually Added Products')]
        ];
    }
}
