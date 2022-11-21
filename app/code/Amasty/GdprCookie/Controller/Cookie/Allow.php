<?php

declare(strict_types=1);

namespace Amasty\GdprCookie\Controller\Cookie;

use Amasty\GdprCookie\Model\CookieConsentLogger;
use Amasty\GdprCookie\Model\CookieManager;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RawFactory;

class Allow implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    public function __construct(
        CookieManager $cookieManager,
        RawFactory $rawFactory,
        Session $session,
        CookieConsentLogger $consentLogger
    ) {
        $this->cookieManager = $cookieManager;
        $this->rawFactory = $rawFactory;
        $this->session = $session;
        $this->consentLogger = $consentLogger;
    }

    public function execute()
    {
        $customerId = (int)$this->session->getCustomerId();
        $this->consentLogger->logCookieConsent($customerId, []);

        $this->cookieManager->updateAllowedCookies(CookieManager::ALLOWED_ALL);

        return $this->rawFactory->create();
    }
}
