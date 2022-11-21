<?php

declare(strict_types=1);

namespace Amasty\GdprCookie\Block;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Layout\LayoutProcessorInterface;
use Amasty\GdprCookie\ViewModel\CookieBar as CookieBarViewModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

class CookieBar extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GdprCookie::cookiebar.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var CookieBarViewModel
     */
    private $viewModel;

    /**
     * @var LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    /**
     * @var Template[]
     */
    private $childBlocks;

    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context,
        Json $jsonSerializer,
        CookieBarViewModel $viewModel,
        array $layoutProcessors = [],
        array $childBlocks = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
        $this->configProvider = $configProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->viewModel = $viewModel;
        $this->childBlocks = $childBlocks;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
    }

    public function getJsLayoutComponent(string $component)
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return $this->jsonSerializer->serialize($this->jsLayout[$component] ?? []);
    }

    public function getViewModel(): CookieBarViewModel
    {
        return $this->viewModel;
    }

    /**
     * @return int
     */
    public function getNoticeType()
    {
        return (int)$this->configProvider->getCookiePrivacyBarType();
    }

    /**
     * @return null|string
     */
    public function getPolicyTextColor()
    {
        return $this->configProvider->getPolicyTextColor();
    }

    /**
     * @return null|string
     */
    public function getBackgroundColor()
    {
        return $this->configProvider->getBackgroundColor();
    }

    /**
     * @return null|string
     */
    public function getAcceptButtonColor()
    {
        return $this->configProvider->getAcceptButtonColor();
    }

    /**
     * @return null|string
     */
    public function getAcceptButtonColorHover()
    {
        return $this->configProvider->getAcceptButtonColorHover();
    }

    /**
     * @return null|string
     */
    public function getAcceptTextColor()
    {
        return $this->configProvider->getAcceptTextColor();
    }

    /**
     * @return null|string
     */
    public function getAcceptTextColorHover()
    {
        return $this->configProvider->getAcceptTextColorHover();
    }

    /**
     * @return null|string
     */
    public function getAcceptButtonOrder()
    {
        return $this->configProvider->getAcceptButtonOrder();
    }

    /**
     * @return null|string
     */
    public function getLinksColor()
    {
        return $this->configProvider->getLinksColor();
    }

    /**
     * @return null|string
     */
    public function getBarLocation()
    {
        return $this->configProvider->getBarLocation();
    }

    /**
     * @return null|string
     */
    public function getSettingsButtonColor()
    {
        return $this->configProvider->getSettingsButtonColor();
    }

    /**
     * @return null|string
     */
    public function getSettingsButtonColorHover()
    {
        return $this->configProvider->getSettingsButtonColorHover();
    }

    /**
     * @return null|string
     */
    public function getSettingsTextColor()
    {
        return $this->configProvider->getSettingsTextColor();
    }

    /**
     * @return null|string
     */
    public function getSettingsTextColorHover()
    {
        return $this->configProvider->getSettingsTextColorHover();
    }

    /**
     * @return null|string
     */
    public function getSettingsButtonOrder()
    {
        return $this->configProvider->getSettingsButtonOrder();
    }

    /**
     * @return null|string
     */
    public function getTitleTextColor()
    {
        return $this->configProvider->getTitleTextColor();
    }

    /**
     * @return null|string
     */
    public function getDescriptionTextColor()
    {
        return $this->configProvider->getDescriptionTextColor();
    }

    /**
     * @return null|string
     */
    public function getDeclineButtonColor()
    {
        return $this->configProvider->getDeclineButtonColor();
    }

    /**
     * @return null|string
     */
    public function getDeclineButtonColorHover()
    {
        return $this->configProvider->getDeclineButtonColorHover();
    }

    /**
     * @return null|string
     */
    public function getDeclineTextColor()
    {
        return $this->configProvider->getDeclineTextColor();
    }

    /**
     * @return null|string
     */
    public function getDeclineTextColorHover()
    {
        return $this->configProvider->getDeclineTextColorHover();
    }

    /**
     * @return null|string
     */
    public function getDeclineButtonName()
    {
        return $this->configProvider->getDeclineButtonName();
    }

    /**
     * @return null|string
     */
    public function getDeclineButtonOrder()
    {
        return $this->configProvider->getDeclineButtonOrder();
    }

    public function getChildBlocksHtml()
    {
        $html = '';
        foreach ($this->childBlocks as $block) {
            try {
                $html .= $block->toHtml();
            } catch (\Throwable $e) {
                $html .= '';
            }
        }

        return $html;
    }

    protected function _toHtml()
    {
        $html = '';
        if ($this->viewModel->getNotificationText()) {
            $html = parent::_toHtml();
        }

        return $html;
    }
}
