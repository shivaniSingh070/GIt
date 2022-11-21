<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Api\GiftCardOrderRepositoryInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountTransactionProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\PaymentException;
use Magento\Sales\Api\Data\OrderInterface;

class ProcessOrderPlace implements ObserverInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var GiftCardOrderRepositoryInterface
     */
    private $gCardOrderRepository;

    /**
     * @var GiftCardCartProcessor
     */
    private $cardCartProcessor;

    /**
     * @var GiftCardAccountTransactionProcessor
     */
    private $giftCardAccountTransactionProcessor;

    public function __construct(
        Repository $accountRepository,
        GiftCardOrderRepositoryInterface $gCardOrderRepository,
        GiftCardCartProcessor $cardCartProcessor,
        GiftCardAccountTransactionProcessor $giftCardAccountTransactionProcessor
    ) {
        $this->accountRepository = $accountRepository;
        $this->gCardOrderRepository = $gCardOrderRepository;
        $this->cardCartProcessor = $cardCartProcessor;
        $this->giftCardAccountTransactionProcessor = $giftCardAccountTransactionProcessor;
    }

    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $observer->getEvent()->getAddress();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        if (!$address) {
            // Single address checkout.
            $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        }
        $extension = $order->getExtensionAttributes();

        /** @var \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface $gCardOrder */
        $gCardOrder = $this->gCardOrderRepository->getEmptyOrderModel();
        $gCardOrder->setGiftCards($address->getAmGiftCards() ?? []);
        $gCardOrder->setGiftAmount((float)$address->getAmGiftCardsAmount());
        $gCardOrder->setBaseGiftAmount((float)$address->getBaseAmGiftCardsAmount());
        $amount = $baseAmount = 0;
        $appliedAccounts = $invalidAppliedCodes = [];
        foreach ($gCardOrder->getGiftCards() as $card) {
            try {
                $account = $this->accountRepository->getById((int)$card[GiftCardCartProcessor::GIFT_CARD_ID]);
                if (!$this->giftCardAccountTransactionProcessor->startTransaction($account)) {
                    $invalidAppliedCodes[] = $card[GiftCardCartProcessor::GIFT_CARD_CODE];
                    $this->cardCartProcessor->removeFromCart($account, $quote);
                    continue;
                }

                $account->setCurrentValue(
                    (float)($account->getCurrentValue() - $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT])
                );
                $appliedAccounts[] = $account;
                $amount += $card[GiftCardCartProcessor::GIFT_CARD_AMOUNT];
                $baseAmount += $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT];
            } catch (\Exception $e) {
                null;
            }
        }

        if (!empty($invalidAppliedCodes)) {
            throw new PaymentException(__('Gift Card processing error: %1', implode(', ', $invalidAppliedCodes)));
        }

        $gCardOrder->setGiftAmount((float)$amount);
        $gCardOrder->setBaseGiftAmount((float)$baseAmount);
        $gCardOrder->setAppliedAccounts($appliedAccounts);

        $extension->setAmGiftcardOrder($gCardOrder);
        $order->setExtensionAttributes($extension);
    }
}
