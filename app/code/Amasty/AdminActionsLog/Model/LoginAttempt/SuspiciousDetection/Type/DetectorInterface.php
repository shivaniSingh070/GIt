<?php

namespace Amasty\AdminActionsLog\Model\LoginAttempt\SuspiciousDetection\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;

interface DetectorInterface
{
    public function isSuspiciousAttempt(LoginAttemptInterface $loginAttempt): bool;
}
