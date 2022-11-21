<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Restoring;

use Amasty\AdminActionsLog\Api\Data\LogDetailInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Restoring\Entity\RestoreHandlerProvider;

class RestoreProcessor
{
    /**
     * @var RestoreHandlerProvider
     */
    private $restoreHandlerProvider;

    public function __construct(
        RestoreHandlerProvider $restoreHandlerProvider
    ) {
        $this->restoreHandlerProvider = $restoreHandlerProvider;
    }

    public function restoreChanges(LogEntryInterface $logEntry): bool
    {
        $groupedLogDetails = $this->prepareLogDetails($logEntry);

        foreach ($groupedLogDetails as $modelName => $logDetails) {
            $this->restoreHandlerProvider->get($modelName)->restore($logEntry, $logDetails);
        }

        return true;
    }

    private function prepareLogDetails(LogEntryInterface $logEntry): array
    {
        $groupedLogDetails = [];
        /** @var LogDetailInterface $logDetail */
        foreach ($logEntry->getLogDetails() as $logDetail) {
            $groupedLogDetails[$logDetail->getModel()][] = $logDetail;
        }

        return $groupedLogDetails;
    }
}
