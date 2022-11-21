<?php
declare(strict_types=1);

namespace Amasty\GiftCardSmsNotifications\Api;

interface SenderInterface
{
    public function isNeedSend(string $notificationType, int $storeId): bool;

    public function send(string $recipientPhone, string $message, string $dltid): void;
}
