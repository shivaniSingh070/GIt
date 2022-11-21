<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail as VisitHistoryDetailModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VisitHistoryDetail extends AbstractDb
{
    const TABLE_NAME = 'amasty_audit_visit_details';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, VisitHistoryDetailModel::ID);
    }
}
