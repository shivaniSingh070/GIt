<?php

namespace Amasty\GdprCookie\Plugin;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\CookiePolicy;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProcessPageResult
{
    public const GOOGLE_TAG_MANAGER_REG = '/\'https:\/\/www\.googletagmanager\.com\/gtm\.js\?id=.*?;/is';
    public const COOKIE_GA = '_ga';
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CookiePolicy
     */
    private $cookiePolicy;

    public function __construct(
        ConfigProvider $configProvider,
        CookieManager $cookieManager,
        CookieManagementInterface $cookieManagement,
        StoreManagerInterface $storeManager,
        CookiePolicy $cookiePolicy
    ) {
        $this->configProvider = $configProvider;
        $this->cookieManager = $cookieManager;
        $this->cookieManagement = $cookieManagement;
        $this->storeManager = $storeManager;
        $this->cookiePolicy = $cookiePolicy;
    }

    public function aroundRenderResult(ResultInterface $subject, \Closure $proceed, ResponseInterface $response)
    {
        /** @var ResultInterface $result */
        $result = $proceed($response);
        $storeId = $this->storeManager->getStore()->getId();
        $allowedGroups = $this->cookieManager->getAllowCookies();
        $replaceGa = $isGaEssential = false;

        foreach ($this->cookieManagement->getEssentialCookies($storeId) as $essentialCookie) {
            if ($essentialCookie->getName() === self::COOKIE_GA) {
                $isGaEssential = true;
            }
        }

        if (!$this->cookiePolicy->isCookiePolicyAllowed() || $allowedGroups === CookieManager::ALLOWED_ALL) {
            return $result;
        }

        if ((!$allowedGroups || $allowedGroups === CookieManager::ALLOWED_NONE) && !$isGaEssential) {
            $replaceGa = true;
        }

        if ($allowedGroups) {
            $allowedGroupIds = array_map('trim', explode(',', $allowedGroups));
            $rejectedCookies = $this->cookieManagement->getNotAssignedCookiesToGroups($storeId, $allowedGroupIds);

            foreach ($rejectedCookies as $cookie) {
                if ($cookie->getName() === self::COOKIE_GA) {
                    $replaceGa = true;
                    break;
                }
            }
        }

        if ($replaceGa) {
            $output = $response->getBody();

            if (preg_match(self::GOOGLE_TAG_MANAGER_REG, $output, $match)) {
                $output = preg_replace(self::GOOGLE_TAG_MANAGER_REG, "'';", $output);
                $response->setBody($output);
            }
        }

        return $result;
    }
}
