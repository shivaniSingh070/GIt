<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Notification\Notifier;

use Amasty\GiftCard\Api\Data\GiftCardEmailInterfaceFactory;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Utils\EmailSender;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

class GiftCardExpirationNotifier implements GiftCardNotifierInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GiftCardEmailInterfaceFactory
     */
    private $cardEmailFactory;

    /**
     * @var EmailSender
     */
    private $emailSender;

    public function __construct(
        ConfigProvider $configProvider,
        GiftCardEmailInterfaceFactory $cardEmailFactory,
        EmailSender $emailSender
    ) {
        $this->configProvider = $configProvider;
        $this->cardEmailFactory = $cardEmailFactory;
        $this->emailSender = $emailSender;
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

        $giftCardRecipientName = $giftCardRecipientName
            ?: $productOptions[GiftCardOptionInterface::RECIPIENT_NAME] ?? '';
        $giftCardRecipientEmail = $giftCardRecipientEmail
            ?: $productOptions[GiftCardOptionInterface::RECIPIENT_EMAIL] ?? '';

        if (!$giftCardRecipientEmail) {
            return; //printed cards don't have recipient email
        }
        $cardEmail = $this->cardEmailFactory->create()
            ->setRecipientName($giftCardRecipientName)
            ->setGiftCode($account->getCodeModel()->getCode())
            ->setExpiryDays($this->configProvider->getNotifyExpiresDateDays($storeId));

        $this->emailSender->sendEmail(
            [[$giftCardRecipientEmail, $giftCardRecipientName]],
            $this->configProvider->getEmailSender($storeId),
            $storeId,
            $this->configProvider->getEmailExpirationTemplate($storeId),
            ['gcard_email' => $cardEmail]
        );
    }
}
