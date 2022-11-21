<?php

namespace Amasty\AdminActionsLog\Model\LoginAttempt\Notification\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;

interface AttemptNotificatorInterface
{
    /**
     * Performs Login Attempt notification.
     *
     * @param LoginAttemptInterface $loginAttempt
     */
    public function execute(LoginAttemptInterface $loginAttempt): void;
}
