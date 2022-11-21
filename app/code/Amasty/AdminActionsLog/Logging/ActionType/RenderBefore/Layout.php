<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\ActionType\RenderBefore;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterfaceFactory;
use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Model\Admin\SessionUserDataProvider;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Layout implements LoggingActionInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var SessionUserDataProvider
     */
    private $userDataProvider;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    /**
     * @var VisitHistoryDetailInterfaceFactory
     */
    private $detailFactory;

    /**
     * @var VisitHistoryEntryRepositoryInterface
     */
    private $historyEntryRepository;

    public function __construct(
        DateTime $dateTime,
        MetadataInterface $metadata,
        SessionUserDataProvider $userDataProvider,
        ObjectDataStorageInterface $dataStorage,
        VisitHistoryDetailInterfaceFactory $detailFactory,
        VisitHistoryEntryRepositoryInterface $historyEntryRepository
    ) {
        $this->dateTime = $dateTime;
        $this->metadata = $metadata;
        $this->userDataProvider = $userDataProvider;
        $this->dataStorage = $dataStorage;
        $this->detailFactory = $detailFactory;
        $this->historyEntryRepository = $historyEntryRepository;
    }

    public function execute(): void
    {
        /** @var \Magento\Framework\View\Element\Template\Context $context */
        if (!$context = $this->metadata->getObject()) {
            return;
        }

        $sessionId = $this->userDataProvider->getSessionId();
        $historyEntry = $this->historyEntryRepository->getBySessionId($sessionId);
        $details = $historyEntry->getVisitHistoryDetails();
        $lastDetail = end($details) ?: $this->detailFactory->create();

        if ($lastDetail->getPageUrl() !== $context->getUrlBuilder()->getCurrentUrl()) {
            $storageKey = $this->userDataProvider->getUserName() . 'StayDuration';
            $currentTimeStamp = $this->dateTime->gmtTimestamp();
            $lastDetailStayDuration = $this->dataStorage->isExists($storageKey)
                ? $currentTimeStamp - ($this->dataStorage->get($storageKey)['timestamp'] ?? 0)
                : 0;
            $lastDetail->setStayDuration((int)$lastDetailStayDuration);
            $this->dataStorage->set($storageKey, ['timestamp' => $currentTimeStamp]);
            $details[] = $this->detailFactory->create(['data' => [
                VisitHistoryDetail::PAGE_NAME => $context->getPageConfig()->getTitle()->get(),
                VisitHistoryDetail::PAGE_URL => $context->getUrlBuilder()->getCurrentUrl()
            ]]);
            $historyEntry->setVisitHistoryDetails($details);
            $this->historyEntryRepository->save($historyEntry);
        }
    }
}
