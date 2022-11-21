<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Api;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface VisitHistoryEntryFinderInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VisitHistoryEntrySearchResultsInterface;
}
