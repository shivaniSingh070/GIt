<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\LogEntry;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Magento\Framework\Model\AbstractModel;

class LogEntry extends AbstractModel implements LogEntryInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const DATE = 'date';
    const USERNAME = 'username';
    const TYPE = 'type';
    const CATEGORY = 'category';
    const CATEGORY_NAME = 'category_name';
    const PARAMETER_NAME = 'parameter_name';
    const ELEMENT_ID = 'element_id';
    const ITEM = 'item';
    const IP = 'ip';
    const STORE_ID = 'store_id';
    const LOG_DETAILS = 'log_details';
    /**#@-*/

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\LogEntry::class);
        $this->setIdFieldName(self::ID);
    }

    public function getDate(): ?string
    {
        return $this->_getData(self::DATE);
    }

    public function setDate(string $date): LogEntryInterface
    {
        $this->setData(self::DATE, $date);

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->_getData(self::USERNAME);
    }

    public function setUsername(string $username): LogEntryInterface
    {
        $this->setData(self::USERNAME, $username);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->_getData(self::TYPE);
    }

    public function setType(string $type): LogEntryInterface
    {
        $this->setData(self::TYPE, $type);

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->_getData(self::CATEGORY);
    }

    public function setCategory(string $category): LogEntryInterface
    {
        $this->setData(self::CATEGORY, $category);

        return $this;
    }

    public function getCategoryName(): ?string
    {
        return $this->_getData(self::CATEGORY_NAME);
    }

    public function setCategoryName(string $categoryName): LogEntryInterface
    {
        $this->setData(self::CATEGORY_NAME, $categoryName);

        return $this;
    }

    public function getParameterName(): ?string
    {
        return $this->_getData(self::PARAMETER_NAME);
    }

    public function setParameterName(string $parameterName): LogEntryInterface
    {
        $this->setData(self::PARAMETER_NAME, $parameterName);

        return $this;
    }

    public function getElementId(): int
    {
        return (int)$this->_getData(self::ELEMENT_ID);
    }

    public function setElementId(int $elementId): LogEntryInterface
    {
        $this->setData(self::ELEMENT_ID, $elementId);

        return $this;
    }

    public function getItem(): ?string
    {
        return $this->_getData(self::ITEM);
    }

    public function setItem(string $item): LogEntryInterface
    {
        $this->setData(self::ITEM, $item);

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->_getData(self::IP);
    }

    public function setIp(string $ipAddress): LogEntryInterface
    {
        $this->setData(self::IP, $ipAddress);

        return $this;
    }

    public function getStoreId(): ?int
    {
        return $this->hasData(self::STORE_ID) ? (int)$this->_getData(self::STORE_ID) : null;
    }

    public function setStoreId(int $storeId): LogEntryInterface
    {
        $this->setData(self::STORE_ID, $storeId);

        return $this;
    }

    public function getLogDetails(): array
    {
        return (array)$this->_getData(self::LOG_DETAILS);
    }

    public function setLogDetails(array $logDetails): LogEntryInterface
    {
        $this->setData(self::LOG_DETAILS, $logDetails);

        return $this;
    }
}
