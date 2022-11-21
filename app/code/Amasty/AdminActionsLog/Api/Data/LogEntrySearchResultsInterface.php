<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface LogEntrySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get log entries list.
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface[]
     */
    public function getItems();

    /**
     * Set log entries list.
     *
     * @param \Amasty\AdminActionsLog\Api\Data\LogEntryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
