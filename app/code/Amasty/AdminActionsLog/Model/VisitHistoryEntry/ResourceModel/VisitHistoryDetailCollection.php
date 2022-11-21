<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryDetail as VisitHistoryDetailResource;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail as VisitHistoryDetailModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class VisitHistoryDetailCollection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(VisitHistoryDetailModel::class, VisitHistoryDetailResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
