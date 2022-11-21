<?php
declare(strict_types=1);

namespace Amasty\GiftCardSmsNotifications\Cron;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Collection;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Model\Notification\NotificationsApplier;
use Amasty\GiftCardAccount\Model\Notification\NotifiersProvider;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Amasty\GiftCardSmsNotifications\Model\SmsConfigProvider;
use Amasty\GiftCardSmsNotifications\Model\SmsSender;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class NotifyCardsExpiration
{
    const STATUSES_FOR_NOTIFICATIONS = [
        AccountStatus::STATUS_ACTIVE,
        AccountStatus::STATUS_INACTIVE
    ];

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var NotificationsApplier
     */
    private $notificationsApplier;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SmsConfigProvider
     */
    private $smsConfigProvider;

    /**
     * @var SmsSender
     */
    private $smsSender;

    public function __construct(
        SmsConfigProvider $smsConfigProvider,
        Repository $accountRepository,
        CollectionFactory $accountCollectionFactory,
        NotificationsApplier $notificationsApplier,
        SmsSender $smsSender,
        DateTime $date,
        LoggerInterface $logger
    ) {
        $this->accountRepository = $accountRepository;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->date = $date;
        $this->logger = $logger;
        $this->notificationsApplier = $notificationsApplier;
        $this->smsSender = $smsSender;
        $this->smsConfigProvider = $smsConfigProvider;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->smsSender->isNeedSend(SmsConfigProvider::SMS_EXPIRY_NOTIFICATION_ENABLE)) {
            return;
        }
        $days = $this->smsConfigProvider->getSmsNotifyExpiresDateDays();
        $date = $this->date->gmtDate('Y-m-d', "+{$days} days");
        $dateExpired = [
            'from' => $date." 00:00:00",
            'to'   => $date." 23:59:59",
        ];
        /** @var Collection $collection */
        $collection = $this->accountCollectionFactory->create();
        $collection->addFieldToFilter(GiftCardAccountInterface::EXPIRED_DATE, $dateExpired)
            ->addFieldToFilter(GiftCardAccountInterface::STATUS, ['in' => self::STATUSES_FOR_NOTIFICATIONS])
            ->addFieldToFilter(GiftCardAccountInterface::CURRENT_VALUE, ['gt' => 0])
            ->addFieldToSelect(GiftCardAccountInterface::ACCOUNT_ID);

        foreach ($collection->getData() as $data) {
            try {
                $this->notificationsApplier->apply(
                    NotifiersProvider::EVENT_CARD_EXPIRATION_SMS,
                    $this->accountRepository->getById((int)$data[GiftCardAccountInterface::ACCOUNT_ID])
                );
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
