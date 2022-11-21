<?php

namespace Amasty\AdminActionsLog\Api\Logging;

interface LoggingActionInterface
{
    /**
     * Perform logging action i.e. save product changes, save visit history, etc.
     */
    public function execute(): void;
}
