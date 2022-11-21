<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\LogEntry;

use Amasty\AdminActionsLog\Api\Data\LogDetailInterface;
use Magento\Framework\Model\AbstractModel;

class LogDetail extends AbstractModel implements LogDetailInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const LOG_ID = 'log_id';
    const NAME = 'name';
    const OLD_VALUE = 'old_value';
    const NEW_VALUE = 'new_value';
    const MODEL = 'model';
    /**#@-*/

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\LogDetail::class);
        $this->setIdFieldName(self::ID);
    }

    public function getLogId(): ?int
    {
        return $this->hasData(self::LOG_ID) ? (int)$this->_getData(self::LOG_ID) : null;
    }

    public function setLogId(int $logId): LogDetailInterface
    {
        $this->setData(self::LOG_ID, $logId);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->_getData(self::NAME);
    }

    public function setName(string $name): LogDetailInterface
    {
        $this->setData(self::NAME, $name);

        return $this;
    }

    public function getOldValue(): ?string
    {
        return $this->_getData(self::OLD_VALUE);
    }

    public function setOldValue(string $oldValue): LogDetailInterface
    {
        $this->setData(self::OLD_VALUE, $oldValue);

        return $this;
    }

    public function getNewValue(): ?string
    {
        return $this->_getData(self::NEW_VALUE);
    }

    public function setNewValue(string $newValue): LogDetailInterface
    {
        $this->setData(self::NEW_VALUE, $newValue);

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->_getData(self::MODEL);
    }

    public function setModel(string $model): LogDetailInterface
    {
        $this->setData(self::MODEL, $model);

        return $this;
    }
}
