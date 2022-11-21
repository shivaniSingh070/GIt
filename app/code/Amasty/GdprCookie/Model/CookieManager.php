<?php

declare(strict_types=1);

namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class CookieManager
{
    public const ALLOW_COOKIES = 'amcookie_allowed';
    public const DISALLOWED_COOKIE_NAMES = 'amcookie_disallowed';
    public const ALLOWED_NONE = '-1';
    public const ALLOWED_ALL = '0';

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * Storage for essential cookie names. Must not delete them even if no decision was taken
     * @var array
     */
    private $essentialCookieNames;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        StoreManagerInterface $storeManager,
        CookieManagementInterface $cookieManagement
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->storeManager = $storeManager;
        $this->cookieManagement = $cookieManagement;
    }

    public function getAllowCookies(): string
    {
        return $this->cookieManager->getCookie(self::ALLOW_COOKIES) ?? '';
    }

    public function updateAllowedCookies(string $allowedCookiesString)
    {
        $allowedCookiesIds = array_map('trim', explode(',', $allowedCookiesString));
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain())
            ->setDurationOneYear();

        try {
            $this->cookieManager->setPublicCookie(self::ALLOW_COOKIES, $allowedCookiesString, $cookieMetadata);

            $rejectedCookieNames = [];
            if ($allowedCookiesString !== self::ALLOWED_ALL) {
                $storeId = (int)$this->storeManager->getStore()->getId();
                $rejectedCookies = $this->cookieManagement->getNotAssignedCookiesToGroups($storeId, $allowedCookiesIds);

                foreach ($rejectedCookies as $cookie) {
                    $rejectedCookieNames[] = $cookie->getName();
                }
            }

            $this->cookieManager->setPublicCookie(
                self::DISALLOWED_COOKIE_NAMES,
                implode(',', $rejectedCookieNames),
                $cookieMetadata
            );
        } catch (\Exception $e) {
            null;
        }
    }

    public function deleteCookies(array $cookieNames)
    {
        try {
            foreach ($cookieNames as $cookieName) {
                if (in_array($cookieName, $this->getEssentialCookieNames() ?? [])) {
                    continue;
                }

                if ($this->cookieManager->getCookie($cookieName)) {
                    $cookieMetadata = $this->cookieMetadataFactory
                        ->createPublicCookieMetadata()
                        ->setPath($this->sessionManager->getCookiePath())
                        ->setDomain($this->sessionManager->getCookieDomain());
                    $this->cookieManager->deleteCookie($cookieName, $cookieMetadata);
                }
            }
        } catch (\Exception $e) {
            null;
        }
    }

    private function getEssentialCookieNames()
    {
        if ($this->essentialCookieNames === null) {
            $storeId = (int)$this->storeManager->getStore()->getId();

            foreach ($this->cookieManagement->getEssentialCookies($storeId) as $cookie) {
                $this->essentialCookieNames[] = $cookie->getName();
            }
        }

        return $this->essentialCookieNames;
    }
}
