<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Controller\Adminhtml\LoginAttempts;

use Amasty\AdminActionsLog\Api\LoginAttemptManagerInterface;
use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractLoginAttempts;
use Magento\Backend\App\Action\Context;

class Clear extends AbstractLoginAttempts
{
    const ADMIN_RESOURCE = 'Amasty_AdminActionsLog::clear_logging';

    /**
     * @var LoginAttemptManagerInterface
     */
    private $loginAttemptManager;

    public function __construct(
        Context $context,
        LoginAttemptManagerInterface $loginAttemptManager
    ) {
        parent::__construct($context);
        $this->loginAttemptManager = $loginAttemptManager;
    }
    public function execute()
    {
        $this->loginAttemptManager->clear();
        $this->messageManager->addSuccessMessage(__('Login Attempts Log has been successfully cleared.'));
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
