<?php

namespace Amasty\GdprCookie\Observer;

use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBarStyle;
use Amasty\GdprCookie\Model\ConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;

class ProcessLayoutRenderElement implements ObserverInterface
{
    public const BLOCK_NAME = 'gdprcookie_bar_footer';
    public const BLOCK_PARENT_TOP = 'after.body.start';
    public const BLOCK_PARENT_BOTTOM = 'root';

    /**
     * @var bool
     */
    private $processed = false;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function execute(Observer $observer)
    {
        if (!$this->processed && $this->configProvider->isCookieBarEnabled()) {
            $event = $observer->getEvent();
            /** @var Layout $layout */
            $layout = $event->getLayout();
            $blockParent = self::BLOCK_PARENT_BOTTOM;

            if ((int)$this->configProvider->getBarLocation()
                && $this->configProvider->getCookiePrivacyBarType() === CookiePolicyBarStyle::CONFIRMATION
            ) {
                $blockParent = self::BLOCK_PARENT_TOP;
            }

            if ($layout->hasElement($blockParent)) {
                $layout->addBlock(
                    \Amasty\GdprCookie\Block\CookieBar::class,
                    self::BLOCK_NAME,
                    $blockParent
                );
            }

            $this->processed = true;
        }
    }
}
