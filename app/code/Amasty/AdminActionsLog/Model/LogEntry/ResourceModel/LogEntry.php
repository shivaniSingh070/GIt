<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\LogEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\LogEntry\LogEntry as LogEntryModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LogEntry extends AbstractDb
{
    const TABLE_NAME = 'amasty_audit_log_entry';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, LogEntryModel::ID);
    }
}
