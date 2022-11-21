<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\LogEntry;

use Amasty\AdminActionsLog\Api\Data\LogEntrySearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Log Entry search results.
 */
class LogEntrySearchResults extends SearchResults implements LogEntrySearchResultsInterface
{
}
