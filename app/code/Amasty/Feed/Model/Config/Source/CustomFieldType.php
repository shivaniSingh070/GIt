<?php

namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CustomFieldType implements ArrayInterface
{
    const ATTRIBUTE = 0;
    const CUSTOM_TEXT = 1;

    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = [
            self::ATTRIBUTE => __('Attribute'),
            self::CUSTOM_TEXT => __('Custom Text')
        ];

        return $options;
    }
}
