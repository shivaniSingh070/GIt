<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel;

use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession as ActiveSessionModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ActiveSession extends AbstractDb
{
    const TABLE_NAME = 'amasty_audit_active_sessions';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ActiveSessionModel::ID);
    }
}
