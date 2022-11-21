<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface LoginAttemptSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get login attempts list.
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface[]
     */
    public function getItems();

    /**
     * Set login attempts list.
     *
     * @param \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
