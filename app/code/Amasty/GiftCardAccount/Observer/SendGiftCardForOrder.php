<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\Notification\NotificationsApplier;
use Amasty\GiftCardAccount\Model\Notification\NotifiersProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SendGiftCardForOrder implements ObserverInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var NotificationsApplier
     */
    private $notificationsApplier;

    public function __construct(
        Repository $accountRepository,
        NotificationsApplier $notificationsApplier
    ) {
        $this->accountRepository = $accountRepository;
        $this->notificationsApplier = $notificationsApplier;
    }

    public function execute(Observer $observer)
    {
        $codes = $observer->getEvent()->getData('codes');

        foreach ($codes as $code) {
            try {
                $this->notificationsApplier->apply(
                    NotifiersProvider::EVENT_ORDER_ACCOUNT_CREATE,
                    $this->accountRepository->getByCode($code)
                );
            } catch (\Exception $e) {
                null;
            }
        }
    }
}
