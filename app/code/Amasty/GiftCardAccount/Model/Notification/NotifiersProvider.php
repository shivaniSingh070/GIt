<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Notification;

class NotifiersProvider
{
    const EVENT_ORDER_ACCOUNT_CREATE = 'order_account_create';
    const EVENT_ADMIN_ACCOUNT_SEND = 'admin_account_send';
    const EVENT_ADMIN_ACCOUNT_SEND_SMS = 'admin_account_send_sms';
    const EVENT_CARD_EXPIRATION_SMS = 'card_expiration_sms';
    const EVENT_CARD_EXPIRATION = 'card_expiration';
    const EVENT_BALANCE_CHANGE = 'balance_change';

    /**
     * @var array
     */
    private $notifiers;

    public function __construct(
        array $notifiers = []
    ) {
        $this->initializeNotifiers($notifiers);
    }

    /**
     * @param string $event
     * @return Notifier\GiftCardNotifierInterface[]
     */
    public function get(string $event): array
    {
        return $this->notifiers[$event] ?? [];
    }

    private function initializeNotifiers(array $notifiers): void
    {
        foreach ($notifiers as $event => $eventNotifiers) {
            foreach ($eventNotifiers as $notifier) {
                if (!$notifier instanceof Notifier\GiftCardNotifierInterface) {
                    throw new \LogicException(
                        sprintf('Notifier must implement %s', Notifier\GiftCardNotifierInterface::class)
                    );
                }
            }
            $this->notifiers[$event] = $eventNotifiers;
        }
    }
}
