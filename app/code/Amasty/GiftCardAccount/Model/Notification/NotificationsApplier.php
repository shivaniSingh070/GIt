<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Notification;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

class NotificationsApplier
{
    /**
     * @var NotifiersProvider
     */
    private $notifiersProvider;

    public function __construct(
        NotifiersProvider $notifiersProvider
    ) {
        $this->notifiersProvider = $notifiersProvider;
    }

    public function apply(
        string $event,
        GiftCardAccountInterface $account,
        string $giftCardRecipientName = null,
        string $giftCardRecipientEmail = null,
        int $storeId = 0
    ): void {
        foreach ($this->notifiersProvider->get($event) as $notifier) {
            $notifier->notify($account, $giftCardRecipientName, $giftCardRecipientEmail, $storeId);
        }
    }
}
