<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Api;

interface LoginAttemptManagerInterface
{
    public function saveAttempt(?string $username, int $status): void;

    public function clear(int $period = null): void;
}
