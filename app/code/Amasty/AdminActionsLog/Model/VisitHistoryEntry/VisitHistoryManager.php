<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface;
use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterfaceFactory;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Api\VisitHistoryManagerInterface;
use Amasty\AdminActionsLog\Model\Admin\SessionUserDataProvider;
use Magento\Framework\Stdlib\DateTime\DateTime;

class VisitHistoryManager implements VisitHistoryManagerInterface
{
    /**
     * @var SessionUserDataProvider
     */
    private $sessionUserDataProvider;

    /**
     * @var VisitHistoryEntryInterfaceFactory
     */
    private $visitHistoryEntryFactory;

    /**
     * @var VisitHistoryEntryRepositoryInterface
     */
    private $visitHistoryEntryRepository;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        SessionUserDataProvider $sessionUserDataProvider,
        VisitHistoryEntryInterfaceFactory $visitHistoryEntryFactory,
        VisitHistoryEntryRepositoryInterface $visitHistoryEntryRepository,
        DateTime $dateTime
    ) {
        $this->sessionUserDataProvider = $sessionUserDataProvider;
        $this->visitHistoryEntryFactory = $visitHistoryEntryFactory;
        $this->visitHistoryEntryRepository = $visitHistoryEntryRepository;
        $this->dateTime = $dateTime;
    }

    public function startVisit(): void
    {
        $userData = $this->sessionUserDataProvider->getUserPreparedData();
        /** @var  $visitHistoryEntryModel VisitHistoryEntryInterface */
        $visitHistoryEntryModel = $this->visitHistoryEntryFactory->create(['data' => $userData]);
        $visitHistoryEntryModel->setSessionStart($this->dateTime->date());

        $this->visitHistoryEntryRepository->save($visitHistoryEntryModel);
    }

    public function endVisit(string $sessionId = null): void
    {
        $sessionId = $sessionId ?: $this->sessionUserDataProvider->getSessionId();
        /** @var  $visitHistoryEntryModel VisitHistoryEntryInterface */
        $visitHistoryEntryModel = $this->visitHistoryEntryRepository->getBySessionId($sessionId);
        $visitHistoryEntryModel->setSessionEnd($this->dateTime->date());
        $this->visitHistoryEntryRepository->save($visitHistoryEntryModel);
    }

    public function clear(int $period = null): void
    {
        $this->visitHistoryEntryRepository->clean($period);
    }
}
