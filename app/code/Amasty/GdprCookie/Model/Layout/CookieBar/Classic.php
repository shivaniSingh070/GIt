<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Model\Layout\CookieBar;

use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBarStyle;
use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Layout\LayoutProcessorInterface;
use Amasty\GdprCookie\ViewModel\CookieBar;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;

class Classic implements LayoutProcessorInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CookieBar
     */
    private $cookieBarViewModel;

    public function __construct(
        ConfigProvider $configProvider,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        CookieBar $cookieBarViewModel
    ) {
        $this->configProvider = $configProvider;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->cookieBarViewModel = $cookieBarViewModel;
    }

    public function process(array $jsLayout): array
    {
        $isPopup = $this->configProvider->getCookiePrivacyBarType()
            == CookiePolicyBarStyle::CONFIRMATION_POPUP;

        $jsLayout = $this->arrayManager->set(
            'classic/components/gdpr-cookie-container',
            $jsLayout,
            [
                'component' => 'Amasty_GdprCookie/js/cookies',
                'policyText' => $this->cookieBarViewModel->getNotificationText(),
                'allowLink' => $this->urlBuilder->getUrl('gdprcookie/cookie/allow'),
                'firstShowProcess' => (string)$this->configProvider->getFirstVisitShow(),
                'barLocation' => $this->configProvider->getBarLocation(),
                'isPopup' => $isPopup,
                'acceptBtnText' => $this->configProvider->getAcceptButtonName(),
                'settingsBtnText' => $this->configProvider->getSettingsButtonName(),
                'declineBtnText' => $this->configProvider->getDeclineButtonName(),
                'isDeclineEnabled' => $this->configProvider->getDeclineEnabled(),
                'additionalClasses' => [
                    '-popup' => $isPopup
                ]
            ]
        );

        return $jsLayout;
    }
}
