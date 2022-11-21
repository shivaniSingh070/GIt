<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Restoring;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;

class RestoreValidator
{
    private $notRestorableCategories;

    public function __construct(array $notRestorableCategories)
    {
        $this->notRestorableCategories = $notRestorableCategories;
    }

    public function isValid(LogEntryInterface $logEntry): bool
    {
        return $logEntry->getType() == LogEntryTypes::TYPE_EDIT
            && !in_array($logEntry->getCategory(), $this->notRestorableCategories);
    }
}
