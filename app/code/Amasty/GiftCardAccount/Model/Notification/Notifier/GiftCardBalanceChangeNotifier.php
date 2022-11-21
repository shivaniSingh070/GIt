<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Notification\Notifier;

use Amasty\GiftCard\Api\Data\GiftCardEmailInterfaceFactory;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Config\Source\Usage;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Utils\EmailSender;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Magento\Framework\Locale\CurrencyInterface;

class GiftCardBalanceChangeNotifier implements GiftCardNotifierInterface
{
    /**
     * @var GiftCardEmailInterfaceFactory
     */
    private $cardEmailFactory;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        GiftCardEmailInterfaceFactory $cardEmailFactory,
        EmailSender $emailSender,
        CurrencyInterface $localeCurrency,
        ConfigProvider $configProvider
    ) {
        $this->cardEmailFactory = $cardEmailFactory;
        $this->emailSender = $emailSender;
        $this->localeCurrency = $localeCurrency;
        $this->configProvider = $configProvider;
    }

    public function notify(
        GiftCardAccountInterface $account,
        string $giftCardRecipientName = null,
        string $giftCardRecipientEmail = null,
        int $storeId = 0
    ): void {
        if (!($orderItem = $account->getOrderItem())) {
            return;
        }

        $productOptions = $orderItem->getProductOptions();
        $storeId = $storeId ?: (int)$orderItem->getStoreId();

        if (!$this->configProvider->isNotifyBalanceChange($storeId)
            || $account->getUsage() == Usage::SINGLE
        ) {
            return;
        }

        $giftCardRecipientEmail = $giftCardRecipientEmail
            ?: $productOptions[GiftCardOptionInterface::RECIPIENT_EMAIL] ?? '';
        if (!$giftCardRecipientEmail) {
            return;
        }

        $giftCardRecipientName = $giftCardRecipientName
            ?: $productOptions[GiftCardOptionInterface::RECIPIENT_NAME] ?? $giftCardRecipientEmail;

        $cardEmail = $this->cardEmailFactory->create()
            ->setRecipientName($giftCardRecipientName)
            ->setGiftCode($account->getCodeModel()->getCode())
            ->setBalance(
                $this->localeCurrency->getCurrency($orderItem->getStore($storeId)->getBaseCurrencyCode())
                    ->toCurrency($account->getCurrentValue())
            );

        $this->emailSender->sendEmail(
            [[$giftCardRecipientEmail, $giftCardRecipientName]],
            $this->configProvider->getEmailSender($storeId),
            $storeId,
            $this->configProvider->getEmailBalanceTemplate($storeId),
            ['gcard_email' => $cardEmail]
        );
    }
}
