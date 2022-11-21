<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\ActionType;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;

class Dummy implements LoggingActionInterface
{
    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function execute(): void
    {
    }
}
