<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Model\Layout\CookieBar;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Layout\LayoutProcessorInterface;
use Amasty\GdprCookie\ViewModel\CookieBar;
use Magento\Framework\Stdlib\ArrayManager;

class Modal implements LayoutProcessorInterface
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
     * @var CookieBar
     */
    private $cookieBarViewModel;

    public function __construct(
        ConfigProvider $configProvider,
        ArrayManager $arrayManager,
        CookieBar $cookieBarViewModel
    ) {
        $this->configProvider = $configProvider;
        $this->arrayManager = $arrayManager;
        $this->cookieBarViewModel = $cookieBarViewModel;
    }

    public function process(array $jsLayout): array
    {
        $jsLayout = $this->arrayManager->set(
            'modal/components/gdpr-cookie-modal',
            $jsLayout,
            [
                'component' => 'Amasty_GdprCookie/js/modal',
                'cookieText' => $this->cookieBarViewModel->getNotificationText(),
                'firstShowProcess' => (string)$this->configProvider->getFirstVisitShow(),
                'acceptBtnText' => $this->configProvider->getAcceptButtonName(),
                'declineBtnText' => $this->configProvider->getDeclineButtonName(),
                'settingsBtnText' => $this->configProvider->getSettingsButtonName(),
                'isDeclineEnabled' => $this->configProvider->getDeclineEnabled()
            ]
        );

        return $jsLayout;
    }
}
