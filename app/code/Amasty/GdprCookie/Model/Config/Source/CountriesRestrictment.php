<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CountriesRestrictment implements OptionSourceInterface
{
    public const ALL_COUNTRIES = 0;
    public const EEA_COUNTRIES = 1;
    public const SPECIFIED_COUNTRIES = 2;

    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = [
                'label' => $label,
                'value' => $value
            ];
        }
        return $result;
    }

    public function toArray(): array
    {
        return [
            self::ALL_COUNTRIES => __('All Countries'),
            self::EEA_COUNTRIES => __('EEA Countries'),
            self::SPECIFIED_COUNTRIES => __('Specified Countries')
        ];
    }
}
