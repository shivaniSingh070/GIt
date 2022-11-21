<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Controller\Adminhtml\VisitHistory;

use Amasty\AdminActionsLog\Api\VisitHistoryManagerInterface;
use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractVisitHistory;
use Magento\Backend\App\Action\Context;

class Clear extends AbstractVisitHistory
{
    const ADMIN_RESOURCE = 'Amasty_AdminActionsLog::clear_logging';

    /**
     * @var VisitHistoryManagerInterface
     */
    private $visitHistoryManager;

    public function __construct(
        Context $context,
        VisitHistoryManagerInterface $visitHistoryManager
    ) {
        parent::__construct($context);
        $this->visitHistoryManager = $visitHistoryManager;
    }

    public function execute()
    {
        $this->visitHistoryManager->clear();
        $this->messageManager->addSuccessMessage(__('Page History Log has been successfully cleared.'));
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
