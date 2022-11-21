<?php
declare(strict_types=1);

namespace Amasty\GiftCardSmsNotifications\Model\Notification\Notifier;

use Amasty\GiftCard\Api\Data\GiftCardEmailInterfaceFactory;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\Notification\Notifier\GiftCardNotifierInterface;
use Amasty\GiftCardSmsNotifications\Api\SenderInterface;
use Amasty\GiftCardSmsNotifications\Model\SmsConfigProvider;
use Magento\Email\Model\Template\FilterFactory;
use Magento\Store\Model\StoreManagerInterface;

class GiftCardExpirationNotifier implements GiftCardNotifierInterface
{
    /**
     * @var GiftCardEmailInterfaceFactory
     */
    private $cardEmailFactory;

    /**
     * @var SmsConfigProvider
     */
    private $smsConfigProvider;

    /**
     * @var FilterFactory
     */
    private $emailFilterFactory;

    /**
     * @var SenderInterface
     */
    private $sender;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        GiftCardEmailInterfaceFactory $cardEmailFactory,
        SmsConfigProvider $smsConfigProvider,
        FilterFactory $emailFilterFactory,
        SenderInterface $sender,
        StoreManagerInterface $storeManager
    ) {
        $this->cardEmailFactory = $cardEmailFactory;
        $this->smsConfigProvider = $smsConfigProvider;
        $this->emailFilterFactory = $emailFilterFactory;
        $this->sender = $sender;
        $this->storeManager = $storeManager;
    }

    public function notify(
        GiftCardAccountInterface $account,
        string $giftCardRecipientName = null,
        string $giftCardRecipientEmail = null,
        int $storeId = 0
    ): void {
        if (!$this->sender->isNeedSend(SmsConfigProvider::SMS_EXPIRY_NOTIFICATION_ENABLE, $storeId)
            || !$recipientPhone = $account->getRecipientPhone()
        ) {
            return;
        }

        $store = $account->getOrderItem()
            ? $account->getOrderItem()->getStore()
            : $this->storeManager->getStore($storeId);
        $cardEmail = $this->cardEmailFactory->create()
            ->setGiftCode($account->getCodeModel()->getCode())
            ->setExpiryDays($this->smsConfigProvider->getNotifyExpiresDateDays($storeId));
        $emailFilter = $this->emailFilterFactory->create();
        $emailFilter->setVariables([
            'gcard_email' => $cardEmail,
            'store' => $store
        ]);
        $configMessage = $this->smsConfigProvider->getSmsExpiryNotificationTemplate($storeId);
        $resultMessage = $emailFilter->filter($configMessage);
        $dltid = $this->smsConfigProvider->getSmsExpiryNotificationDltid($storeId);
        $this->sender->send($recipientPhone, $resultMessage, $dltid);
    }
}
