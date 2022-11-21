<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Api;

interface ActiveSessionManagerInterface
{
    public function initNew(): void;

    public function update(): void;

    public function terminate(string $sessionId = null): void;

    public function getInactiveSessions(): array;
}
