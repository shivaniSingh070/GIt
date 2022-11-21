<?php

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountTransactionProcessor;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class SaveAppliedAccounts implements ObserverInterface
{
    /**
     * @var GiftCardAccount\Repository
     */
    private $accountRepository;

    /**
     * @var GiftCardAccountTransactionProcessor
     */
    private $giftCardAccountTransactionProcessor;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        GiftCardAccount\Repository $accountRepository,
        GiftCardAccountTransactionProcessor $giftCardAccountTransactionProcessor,
        ManagerInterface $eventManager
    ) {
        $this->accountRepository = $accountRepository;
        $this->giftCardAccountTransactionProcessor = $giftCardAccountTransactionProcessor;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Observer $observer
     * @return void|null
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order === null) {
            $orders = $observer->getEvent()->getOrders();
            //multiple shipping checkout
            foreach ($orders as $order) {
                $this->saveAccount($order);
            }
        } else {
            $this->saveAccount($order);
        }
    }

    private function saveAccount(OrderInterface $order): void
    {
        if (!$order->getExtensionAttributes() || !$order->getExtensionAttributes()->getAmGiftcardOrder()) {
            return;
        }

        /** @var GiftCardOrderInterface $gCardOrder */
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();
        try {
            foreach ($gCardOrder->getAppliedAccounts() as $appliedAccount) {
                $this->eventManager->dispatch(
                    'amasty_giftcard_applied_accounts_before_save',
                    ['order' => $order, 'account' => $appliedAccount]
                );

                $this->accountRepository->save($appliedAccount);
                $this->giftCardAccountTransactionProcessor->completeTransaction($appliedAccount);
            }
        } catch (\Exception $e) {
            null;
        }
    }
}
