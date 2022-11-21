<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class AbstractVisitHistory extends Action
{
    const ADMIN_RESOURCE = 'Amasty_AdminActionsLog::page_visit_history';
}
