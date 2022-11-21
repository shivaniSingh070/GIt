<?php

namespace Amasty\GdprCookie\Model;

use Amasty\Base\Model\GetCustomerIp;
use Amasty\GdprCookie\Api\CookieConsentRepositoryInterface;
use Amasty\GdprCookie\Model\CookieConsent\CookieGroupProcessor;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class CookieConsentLogger
{
    /**
     * @var CookieConsentRepositoryInterface
     */
    private $cookieConsentRepository;

    /**
     * @var CookieConsentFactory
     */
    private $cookieConsentFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var GetCustomerIp
     */
    private $customerIp;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CookieGroupProcessor
     */
    private $cookieGroupProcessor;

    public function __construct(
        CookieConsentRepositoryInterface $cookieConsentRepository,
        CookieConsentFactory $cookieConsentFactory,
        GetCustomerIp $customerIp,
        StoreManagerInterface $storeManager,
        DateTime $date,
        ConfigProvider $configProvider,
        CookieGroupProcessor $cookieGroupProcessor
    ) {
        $this->cookieConsentRepository = $cookieConsentRepository;
        $this->cookieConsentFactory = $cookieConsentFactory;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->customerIp = $customerIp;
        $this->configProvider = $configProvider;
        $this->cookieGroupProcessor = $cookieGroupProcessor;
    }

    public function logCookieConsent(?int $customerId, ?array $allowedCookieGroupIds = null)
    {
        if (!$customerId && !$this->configProvider->isLogGuest()) {
            return;
        }

        $cookieConsent = $this->cookieConsentRepository->getByCustomerId($customerId);
        $website = $this->storeManager->getWebsite()->getId();
        $customerIp = $this->getRemoteIp();
        $groupsStatus = $this->cookieGroupProcessor->getGroupsStatus($allowedCookieGroupIds);

        if ($cookieConsent && $customerId) {
            $cookieConsent->setCustomerId($customerId)
                ->setConsentStatus('') // ToDo: Delete row after removing "ConsentStatus" column
                ->setWebsite($website)
                ->setDateRecieved($this->date->gmtDate())
                ->setCustomerIp($customerIp)
                ->setGroupsStatus($groupsStatus)
                ->setAllowedGroupIds($allowedCookieGroupIds);
            $this->cookieConsentRepository->save($cookieConsent);

            return;
        }

        $consent = $this->cookieConsentFactory->create();
        $consent->setCustomerId($customerId)
            ->setConsentStatus('') // ToDo: Delete row after removing "ConsentStatus" column
            ->setWebsite($this->storeManager->getWebsite()->getId())
            ->setCustomerIp($this->getRemoteIp())
            ->setGroupsStatus($groupsStatus)
            ->setAllowedGroupIds($allowedCookieGroupIds);
        $this->cookieConsentRepository->save($consent);
    }

    public function getRemoteIp()
    {
        $ip = $this->customerIp->getCurrentIp();
        $ip = substr($ip, 0, strrpos($ip, ".")) . '.0';

        return $ip;
    }
}
