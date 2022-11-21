<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Visit History Entry search results.
 */
class VisitHistoryEntrySearchResults extends SearchResults implements VisitHistoryEntrySearchResultsInterface
{
}
