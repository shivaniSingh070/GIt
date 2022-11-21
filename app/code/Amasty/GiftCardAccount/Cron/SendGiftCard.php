<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Cron;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Collection;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Model\Notification\NotificationsApplier;
use Amasty\GiftCardAccount\Model\Notification\NotifiersProvider;
use Magento\Framework\Stdlib\DateTime\DateTime;

class SendGiftCard
{
    /**
     * @var CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var NotificationsApplier
     */
    private $notificationsApplier;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Repository
     */
    private $accountRepository;

    public function __construct(
        CollectionFactory $accountCollectionFactory,
        ConfigProvider $configProvider,
        NotificationsApplier $notificationsApplier,
        DateTime $date,
        Repository $accountRepository
    ) {
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->configProvider = $configProvider;
        $this->notificationsApplier = $notificationsApplier;
        $this->date = $date;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function execute()
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }
        $currentDate = $this->date->gmtDate('Y-m-d H:i:s');
        /** @var Collection $collection */
        $collection = $this->accountCollectionFactory->create();
        $collection->addFieldToFilter(GiftCardAccountInterface::DATE_DELIVERY, ['lteq' => $currentDate])
            ->addFieldToFilter('is_sent', 0)
            ->addFieldToFilter('order_item_id', ['notnull' => true])
            ->addFieldToSelect(GiftCardAccountInterface::ACCOUNT_ID);

        foreach ($collection->getData() as $data) {
            $this->notificationsApplier->apply(
                NotifiersProvider::EVENT_ORDER_ACCOUNT_CREATE,
                $this->accountRepository->getById((int)$data[GiftCardAccountInterface::ACCOUNT_ID])
            );
        }
    }
}
