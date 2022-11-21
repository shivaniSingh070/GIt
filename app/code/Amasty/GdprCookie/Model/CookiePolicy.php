<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Model;

use Amasty\Base\Model\GetCustomerIp;
use Amasty\GdprCookie\Model\Config\Source\CountriesRestrictment;
use Amasty\Geoip\Model\Geolocation;

class CookiePolicy
{
    /**
     * @var GetCustomerIp
     */
    private $getCustomerIp;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * Countries data storage
     *
     * @var array
     */
    private $countries = [];

    public function __construct(
        GetCustomerIp $getCustomerIp,
        Geolocation $geolocation,
        ConfigProvider $configProvider
    ) {
        $this->getCustomerIp = $getCustomerIp;
        $this->geolocation = $geolocation;
        $this->configProvider = $configProvider;
    }

    public function isCookiePolicyAllowed(): bool
    {
        if (!$this->configProvider->isCookieBarEnabled()) {
            return false;
        }
        $country = $this->getCurrentCustomerCountry();

        switch ($this->configProvider->getCookiePolicyBarVisibility()) {
            case CountriesRestrictment::ALL_COUNTRIES:
                return true;
            case CountriesRestrictment::EEA_COUNTRIES:
                return in_array(
                    $country,
                    $this->configProvider->getEuCountriesCodes()
                );
            case CountriesRestrictment::SPECIFIED_COUNTRIES:
                return in_array(
                    $country,
                    $this->configProvider->getCookiePolicyBarCountriesCodes()
                );
        }

        return false;
    }

    protected function getCurrentCustomerCountry(): string
    {
        $ip = $this->getCustomerIp->getCurrentIp();

        if (!isset($this->countries[$ip])) {
            $location = $this->geolocation->locate($ip);
            $this->countries[$ip] = $location->getCountry();
        }

        return (string)$this->countries[$ip];
    }
}
