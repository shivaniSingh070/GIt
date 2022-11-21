<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Api\Data;

interface VisitHistoryEntrySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get visit history list.
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface[]
     */
    public function getItems();

    /**
     * Set visit history list.
     *
     * @param \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
