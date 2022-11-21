<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Notification\Notifier;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

interface GiftCardNotifierInterface
{
    /**
     * Performs specific notification action depending on GiftCardAccount data.
     * $giftCardRecipientName, $giftCardRecipientEmail and $storeId params could be used
     * to replace data from GiftCardAccount
     *
     * @param GiftCardAccountInterface $account
     * @param string|null $giftCardRecipientName
     * @param string|null $giftCardRecipientEmail
     * @param int $storeId
     *
     * @return void
     */
    public function notify(
        GiftCardAccountInterface $account,
        string $giftCardRecipientName = null,
        string $giftCardRecipientEmail = null,
        int $storeId = 0
    ): void;
}
