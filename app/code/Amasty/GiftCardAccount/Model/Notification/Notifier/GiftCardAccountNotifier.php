<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Notification\Notifier;

use Amasty\GiftCard\Api\Data\GiftCardEmailInterface;
use Amasty\GiftCard\Api\Data\GiftCardEmailInterfaceFactory;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\Image\Repository as ImageRepository;
use Amasty\GiftCard\Utils\EmailSender;
use Amasty\GiftCard\Utils\FileUpload;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository as AccountRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Model\StoreManagerInterface;

class GiftCardAccountNotifier implements GiftCardNotifierInterface
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

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var ImageRepository
     */
    private $imageRepository;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        GiftCardEmailInterfaceFactory $cardEmailFactory,
        EmailSender $emailSender,
        CurrencyInterface $localeCurrency,
        ConfigProvider $configProvider,
        FileUpload $fileUpload,
        ImageRepository $imageRepository,
        AccountRepository $accountRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->cardEmailFactory = $cardEmailFactory;
        $this->emailSender = $emailSender;
        $this->localeCurrency = $localeCurrency;
        $this->configProvider = $configProvider;
        $this->fileUpload = $fileUpload;
        $this->imageRepository = $imageRepository;
        $this->accountRepository = $accountRepository;
        $this->storeManager = $storeManager;
    }

    public function notify(
        GiftCardAccountInterface $account,
        string $giftCardRecipientName = null,
        string $giftCardRecipientEmail = null,
        int $storeId = 0
    ): void {
        try {
            if ($account->getOrderItem()) {
                $this->sendByOrderItem($account, $giftCardRecipientName, $giftCardRecipientEmail, $storeId);
            } elseif ($giftCardRecipientEmail) {
                $this->sendByAccountData($account, $giftCardRecipientEmail, $giftCardRecipientName, $storeId);
            }
        } catch (\Exception $e) {
            return;
        }

        $account->setIsSent(true);
        $this->accountRepository->save($account);
    }

    private function sendByOrderItem(
        GiftCardAccountInterface $account,
        string $recipientName = null,
        string $recipientEmail = null,
        int $storeId = 0
    ): void {
        if (!($orderItem = $account->getOrderItem())) {
            return;
        }

        $this->updateProductOptions($orderItem, $recipientName, $recipientEmail);
        $productOptions = $orderItem->getProductOptions();
        $storeId = $storeId ?: (int)$orderItem->getStoreId();
        $cardEmail = $this->prepareGiftCardEmailFromOrderItem($account, $orderItem, $storeId);

        $code = $account->getCodeModel()->getCode();
        $imageUrl = $this->prepareImageUrl((int)$productOptions[GiftCardOptionInterface::IMAGE] ?? 0, $code);

        $cardEmail->setGiftCode($code)->setImage($imageUrl);
        $this->emailSender->sendEmail(
            $this->getRecipients($productOptions, $storeId),
            $this->configProvider->getEmailSender($storeId),
            $storeId,
            $productOptions[Attributes::EMAIL_TEMPLATE] ?? $this->configProvider->getEmailTemplate($storeId),
            ['gcard_email' => $cardEmail],
            $this->fileUpload->getImagePathByUrl($imageUrl)
        );
    }

    private function sendByAccountData(
        GiftCardAccountInterface $account,
        string $recipientEmail,
        string $recipientName = null,
        int $storeId = 0
    ): void {
        $balance = $this->formatBalance($account->getInitialValue(), $storeId);
        $code = $account->getCodeModel()->getCode();
        $imageUrl = $this->prepareImageUrl($account->getImageId(), $code);

        $cardEmail = $this->cardEmailFactory->create()
            ->setRecipientName($recipientName)
            ->setExpiredDate($account->getExpiredDate())
            ->setBalance($balance)
            ->setGiftCode($code)
            ->setImage($imageUrl)
            ->setIsAllowAssignToCustomer($this->configProvider->isAllowAssignToCustomer($storeId));

        $this->emailSender->sendEmail(
            $this->getRecipients([
                GiftCardOptionInterface::RECIPIENT_EMAIL => $recipientEmail,
                GiftCardOptionInterface::RECIPIENT_NAME => $recipientName
            ], $storeId),
            $this->configProvider->getEmailSender($storeId),
            $storeId,
            $this->configProvider->getEmailTemplate($storeId),
            ['gcard_email' => $cardEmail],
            $this->fileUpload->getImagePathByUrl($imageUrl)
        );
    }

    private function updateProductOptions(
        OrderItemInterface $orderItem,
        string $recipientName = null,
        string $recipientEmail = null
    ): void {
        $productOptions = $orderItem->getProductOptions();

        $productOptions[GiftCardOptionInterface::RECIPIENT_NAME] =
            $recipientName ?: $productOptions[GiftCardOptionInterface::RECIPIENT_NAME] ?? '';
        $productOptions[GiftCardOptionInterface::RECIPIENT_EMAIL] =
            $recipientEmail ?: $productOptions[GiftCardOptionInterface::RECIPIENT_EMAIL] ?? '';
        $productOptions[GiftCardOptionInterface::SENDER_NAME] =
            $productOptions[GiftCardOptionInterface::SENDER_NAME] ?? $orderItem->getOrder()->getCustomerName();
        $productOptions[GiftCardOptionInterface::SENDER_EMAIL] =
            $productOptions[GiftCardOptionInterface::SENDER_EMAIL] ?? $orderItem->getOrder()->getCustomerEmail();

        $orderItem->setProductOptions($productOptions);
    }

    private function prepareGiftCardEmailFromOrderItem(
        GiftCardAccountInterface $account,
        OrderItemInterface $orderItem,
        int $storeId = 0
    ): GiftCardEmailInterface {
        $cardEmail = $this->cardEmailFactory->create();
        $balance = $this->formatBalance($account->getInitialValue(), (int)$orderItem->getStoreId());
        $orderItemOptions = $orderItem->getProductOptions();

        $cardEmail->setRecipientName($orderItemOptions[GiftCardOptionInterface::RECIPIENT_NAME])
            ->setSenderName(
                $orderItemOptions[GiftCardOptionInterface::SENDER_NAME]
            )->setSenderEmail(
                $orderItemOptions[GiftCardOptionInterface::SENDER_EMAIL]
            )->setSenderMessage($orderItemOptions[GiftCardOptionInterface::MESSAGE] ?? '')
            ->setExpiredDate($account->getExpiredDate())
            ->setBalance($balance)
            ->setIsAllowAssignToCustomer($this->configProvider->isAllowAssignToCustomer($storeId));

        return $cardEmail;
    }

    private function getRecipients(array $productOptions, int $storeId): array
    {
        $recipients[] = [
            $productOptions[GiftCardOptionInterface::RECIPIENT_EMAIL],
            $productOptions[GiftCardOptionInterface::RECIPIENT_NAME]
        ];
        if ($sendCopyTo = $this->configProvider->getEmailRecipients($storeId)) {
            $recipients = array_merge($recipients, $sendCopyTo);
        }

        return $recipients;
    }

    private function formatBalance(float $amount, int $storeId): string
    {
        return $this->localeCurrency->getCurrency($this->storeManager->getStore($storeId)->getBaseCurrencyCode())
            ->toCurrency($amount);
    }

    private function prepareImageUrl(int $imageId, string $code): string
    {
        $imageUrl = '';

        if ($imageId !== 0) {
            try {
                $image = $this->imageRepository->getById($imageId);
                $imageUrl = $this->fileUpload->getEmailImageUrl($image, $code);
            } catch (LocalizedException $e) {
                null;
            }
        }

        return $imageUrl;
    }
}
