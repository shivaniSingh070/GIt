<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Controller\Adminhtml\ActionsLog;

use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractActionsLog;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;

class Clear extends AbstractActionsLog
{
    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    /**
     * @var Session
     */
    private $backendSession;

    public function __construct(
        Context $context,
        LogEntryRepositoryInterface $logEntryRepository,
        Session $backendSession
    ) {
        parent::__construct($context);
        $this->logEntryRepository = $logEntryRepository;
        $this->backendSession = $backendSession;
    }
    public function execute()
    {
        if ($storeIds = $this->getAvailableStoreIds()) {
            $this->logEntryRepository->cleanByStoreIds($storeIds);
        } else {
            $this->logEntryRepository->clean();
        }
        $this->messageManager->addSuccessMessage(__('Actions Log has been successfully cleared.'));
        $this->_redirect($this->_redirect->getRefererUrl());
    }

    private function getAvailableStoreIds(): array
    {
        $userRole = $this->backendSession->getUser()->getRole();
        if ($userRole->getData('gws_is_all')) {
            return [];
        }

        if ($userRole->getData('gws_stores')) {
            return $userRole->getData('gws_stores');
        }

        return [];
    }
}
