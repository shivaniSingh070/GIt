<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\ActiveSession;

use Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface;
use Magento\Framework\Model\AbstractModel;

class ActiveSession extends AbstractModel implements ActiveSessionInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const SESSION_ID = 'session_id';
    const USERNAME = 'username';
    const FULL_NAME = 'full_name';
    const IP = 'ip';
    const SESSION_START = 'session_start';
    const RECENT_ACTIVITY = 'recent_activity';
    const LOCATION = 'location';
    const COUNTRY_ID = 'country_id';
    /**#@-*/

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\ActiveSession::class);
        $this->setIdFieldName(self::ID);
    }

    public function getSessionId(): ?string
    {
        return $this->_getData(self::SESSION_ID);
    }

    public function setSessionId(string $sessionId): ActiveSessionInterface
    {
        return $this->setData(self::SESSION_ID, $sessionId);
    }

    public function getUsername(): ?string
    {
        return $this->_getData(self::USERNAME);
    }

    public function setUsername(string $username): ActiveSessionInterface
    {
        return $this->setData(self::USERNAME, $username);
    }

    public function getFullName(): ?string
    {
        return $this->_getData(self::FULL_NAME);
    }

    public function setFullName(string $fullName): ActiveSessionInterface
    {
        return $this->setData(self::FULL_NAME, $fullName);
    }

    public function getIp(): ?string
    {
        return $this->_getData(self::IP);
    }

    public function setIp(string $ip): ActiveSessionInterface
    {
        return $this->setData(self::IP, $ip);
    }

    public function getSessionStart(): ?string
    {
        return $this->_getData(self::SESSION_START);
    }

    public function setSessionStart(string $startTime): ActiveSessionInterface
    {
        return $this->setData(self::SESSION_START, $startTime);
    }

    public function getRecentActivity(): ?string
    {
        return $this->_getData(self::RECENT_ACTIVITY);
    }

    public function setRecentActivity(string $activityTime): ActiveSessionInterface
    {
        return $this->setData(self::RECENT_ACTIVITY, $activityTime);
    }

    public function getLocation(): ?string
    {
        return $this->_getData(self::LOCATION);
    }

    public function setLocation(string $location): ActiveSessionInterface
    {
        return $this->setData(self::LOCATION, $location);
    }

    public function getCountryId(): ?string
    {
        return $this->_getData(self::COUNTRY_ID);
    }

    public function setCountryId(string $countryId): ActiveSessionInterface
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }
}
