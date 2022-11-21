<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;

class Usage extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const SINGLE = 'single';
    const MULTIPLE = 'multiple';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    public function toOptionArray()
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::SINGLE => __('Single'),
            self::MULTIPLE => __('Multiple')
        ];
    }

    /**
     * Get value by key
     *
     * @param string $key
     * @return Phrase
     */
    public function getValueByKey(string $key): Phrase
    {
        return isset($this->toArray()[$key]) ? $this->toArray()[$key] : __('Undefined');
    }
}
