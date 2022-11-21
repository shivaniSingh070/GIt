<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Api;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface;
use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface VisitHistoryEntryRepositoryInterface
{
    /**
     * @param int $id
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): VisitHistoryEntryInterface;

    /**
     * @param string $sessionId
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function getBySessionId(string $sessionId): VisitHistoryEntryInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface $logEntry
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(VisitHistoryEntryInterface $logEntry): VisitHistoryEntryInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface $logEntry
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(VisitHistoryEntryInterface $logEntry): bool;

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * @param int|null $period
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function clean(?int $period = null): void;
}
