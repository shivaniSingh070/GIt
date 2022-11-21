<?php
declare(strict_types=1);

namespace Amasty\GiftCardSmsNotifications\Model;

use Amasty\GiftCardSmsNotifications\Api\SenderInterface;
use Amasty\GiftCardSmsNotifications\Model\Smspro\ApicallFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

class SmsSender implements SenderInterface
{
    /**
     * @var ApicallFactory
     */
    private $apicallFactory;

    /**
     * @var SmsConfigProvider
     */
    private $smsConfigProvider;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ApicallFactory $apicallFactory,
        SmsConfigProvider $smsConfigProvider,
        Manager $moduleManager,
        LoggerInterface $logger
    ) {
        $this->apicallFactory = $apicallFactory;
        $this->smsConfigProvider = $smsConfigProvider;
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
    }

    public function isNeedSend(string $notificationType, int $storeId = Store::DEFAULT_STORE_ID): bool
    {
        if (!$this->smsConfigProvider->isEnabled($storeId)
            || !$this->smsConfigProvider->isSmsNotify($storeId)
            || !$this->smsConfigProvider->isSmsNotifyByType($notificationType, $storeId)
            || !$this->moduleManager->isEnabled('Magecomp_Smspro')
        ) {
            return false;
        }

        return true;
    }

    public function send(string $recipientPhone, string $message, string $dltid): void
    {
        $magecompApicall = $this->apicallFactory->create();
        $result = $magecompApicall->callApiUrl($recipientPhone, $message, $dltid);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        switch (gettype($result)) {
            case 'boolean':
                if ($result === false) {
                    throw new LocalizedException(__('Something went wrong with sending sms.'));
                }
                break;
            case 'array':
                if (isset($result['status'], $result['message']) && $result['status'] === false) {
                    throw new LocalizedException(__($result['message']));
                }
                break;
            case 'string':
                $this->logger->error($result);
                throw new LocalizedException(__($result));
        }
    }
}
