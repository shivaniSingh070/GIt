<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Api;

interface VisitHistoryManagerInterface
{
    /**
     * Initialize Visit History tracking for current admin.
     */
    public function startVisit(): void;

    /**
     * Track Visit History end time, i.e. admin logout or session destroy.
     *
     * @param string|null $sessionId
     */
    public function endVisit(string $sessionId = null): void;

    /**
     * Clear whole Visit History storage.
     *
     * @param int|null $period
     */
    public function clear(int $period = null): void;
}
