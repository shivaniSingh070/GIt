<?php

namespace Amasty\GdprCookie\Observer\Customer;

use Amasty\GdprCookie\Model\CookieConsent\CookieGroupProcessor;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Amasty\GdprCookie\Model\CookieManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Login implements ObserverInterface
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    /**
     * @var CookieGroupProcessor
     */
    private $cookieGroupProcessor;

    public function __construct(
        CookieManager $cookieManager,
        CookieConsentLogger $consentLogger,
        CookieGroupProcessor $cookieGroupProcessor
    ) {
        $this->cookieManager = $cookieManager;
        $this->consentLogger = $consentLogger;
        $this->cookieGroupProcessor = $cookieGroupProcessor;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $allowedCookieGroups = $this->cookieManager->getAllowCookies();
        if ($allowedCookieGroups === null) {
            return;
        }

        $this->consentLogger->logCookieConsent(
            (int)$observer->getData('customer')->getData('entity_id'),
            $this->cookieGroupProcessor->getAllowedGroupIds($allowedCookieGroups)
        );
    }
}
