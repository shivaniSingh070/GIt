<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\LogEntry\Frontend;

use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\DiffFinder\DiffFinderAdapterInterface;
use Amasty\AdminActionsLog\Model\LogEntry\LogDetail;

class LogDetailsFormatter
{
    const TAG_INS = 'ins';
    const TAB_DEL = 'del';

    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    /**
     * @var DiffFinderAdapterInterface
     */
    private $diffFinder;

    public function __construct(
        LogEntryRepositoryInterface $logEntryRepository,
        DiffFinderAdapterInterface $diffFinder
    ) {
        $this->logEntryRepository = $logEntryRepository;
        $this->diffFinder = $diffFinder;
    }

    public function format(int $logEntryId): array
    {
        $logEntry = $this->logEntryRepository->getById($logEntryId);

        return array_map(function ($logDetail) {
            $diffString = $this->diffFinder->render(
                (string)$logDetail->getOldValue(),
                (string)$logDetail->getNewValue()
            );

            return [
                LogDetail::NAME => $logDetail->getName(),
                LogDetail::OLD_VALUE => $this->removeDiffTags($diffString, self::TAG_INS),
                LogDetail::NEW_VALUE => $this->removeDiffTags($diffString, self::TAB_DEL),
            ];
        }, $logEntry->getLogDetails());
    }

    private function removeDiffTags(string $text, string $tagName): string
    {
        return (string)preg_replace("/<$tagName>(.*?)<\/$tagName>/s", '', $text);
    }
}
