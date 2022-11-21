<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Cron;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountTransactionProcessor;

class ClearExpiredTransaction
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GiftCardAccountTransactionProcessor
     */
    private $giftCardAccountTransactionProcessor;

    public function __construct(
        ConfigProvider $configProvider,
        GiftCardAccountTransactionProcessor $giftCardAccountTransactionProcessor
    ) {
        $this->configProvider = $configProvider;
        $this->giftCardAccountTransactionProcessor = $giftCardAccountTransactionProcessor;
    }

    public function execute(): void
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        $this->giftCardAccountTransactionProcessor->clearExpiredTransaction();
    }
}
