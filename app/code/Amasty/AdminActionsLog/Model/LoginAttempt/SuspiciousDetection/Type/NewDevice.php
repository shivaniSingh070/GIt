<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\LoginAttempt\SuspiciousDetection\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;

class NewDevice extends AbstractDetection
{
    public function isSuspiciousAttempt(LoginAttemptInterface $loginAttempt): bool
    {
        $lastAttempt = $this->getLastSucceedAttempt($loginAttempt);

        if ($lastAttempt->getId() && !empty($lastAttempt->getUserAgent())) {
            return $loginAttempt->getUserAgent() !== $lastAttempt->getUserAgent();
        }

        return false;
    }
}
