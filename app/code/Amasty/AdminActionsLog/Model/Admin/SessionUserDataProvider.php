<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\Admin;

use Amasty\AdminActionsLog\Model\ConfigProvider;
use Amasty\Geoip\Model\Geolocation;
use Magento\Backend\Model\Auth;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\HTTP;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Locale\ListsInterface;

class SessionUserDataProvider
{
    /**
     * @var HTTP\Header
     */
    private $header;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var Auth\Session
     */
    private $authSession;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var ListsInterface
     */
    private $locateList;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        HTTP\Header $header,
        Geolocation $geolocation,
        Auth\Session $authSession,
        RemoteAddress $remoteAddress,
        ListsInterface $locateList,
        ConfigProvider $configProvider,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->header = $header;
        $this->geolocation = $geolocation;
        $this->authSession = $authSession;
        $this->remoteAddress = $remoteAddress;
        $this->locateList = $locateList;
        $this->configProvider = $configProvider;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function getIpAddress(): ?string
    {
        return $this->remoteAddress->getRemoteAddress() ?: null;
    }

    public function getSessionId(): string
    {
        return $this->authSession->getSessionId();
    }

    public function getUserAgent(): string
    {
        return $this->header->getHttpUserAgent();
    }

    public function getFullUserName(): ?string
    {
        $user = $this->authSession->getUser();

        return $user !== null
            ? $user->getFirstName() . ' ' . $user->getLastName()
            : null;
    }

    public function getUserName(): ?string
    {
        return $this->authSession->getUser()
            ? $this->authSession->getUser()->getUserName()
            : null;
    }

    public function getLocation(?string $ipAddress = null): DataObject
    {
        $location = $this->dataObjectFactory->create();

        if ($this->configProvider->isEnabledGeolocation()) {
            $locationData = $this->geolocation->locate($ipAddress);
            $location->setData([
                'country' => $this->locateList->getCountryTranslation($locationData->getCountry()),
                'city' => $this->locateList->getCountryTranslation($locationData->getCity())
            ]);
        }

        return $location;
    }

    public function getUserPreparedData(): array
    {
        $location = $this->getLocation();

        return [
            'ip' => $this->getIpAddress(),
            'username' => $this->getUserName(),
            'full_name' => $this->getFullUserName(),
            'location' => sprintf('%s %s', $location->getCountry(), $location->getCity()),
            'country_id' => $location->getCountry(),
            'session_id' => $this->getSessionId()
        ];
    }
}
