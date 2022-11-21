<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\ViewModel;

use Amasty\GdprCookie\Model\ConfigProvider;
use Magento\Cms\Model\Template\Filter as CmsTemplateFilter;
use Magento\Framework\Serialize\Serializer\Json;

class CookieBar
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var CmsTemplateFilter
     */
    private $cmsTemplateFilter;

    /**
     * @var string
     */
    private $notificationText;

    public function __construct(
        ConfigProvider $configProvider,
        Json $jsonSerializer,
        CmsTemplateFilter $cmsTemplateFilter
    ) {
        $this->configProvider = $configProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->cmsTemplateFilter = $cmsTemplateFilter;
    }

    public function getNotificationText(): string
    {
        if (null === $this->notificationText) {
            $this->notificationText = $this->cmsTemplateFilter->filter($this->configProvider->getNotificationText());
        }

        return $this->notificationText;
    }
}
