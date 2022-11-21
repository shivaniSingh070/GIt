<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Model\Config\Source;

use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\Data\OptionSourceInterface;

class Countries implements OptionSourceInterface
{
    /**
     * @var Country
     */
    private $country;

    public function __construct(Country $country)
    {
        $this->country = $country;
    }

    public function toOptionArray(): array
    {
        return $this->country->toOptionArray();
    }
}
