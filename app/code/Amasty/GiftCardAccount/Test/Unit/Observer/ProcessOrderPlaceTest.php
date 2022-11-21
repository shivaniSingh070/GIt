<?php

namespace Amasty\GiftCardAccount\Test\Unit\Observer;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountTransactionProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Repository as GiftCardOrderRepository;
use Amasty\GiftCardAccount\Observer\ProcessOrderPlace;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Observer\ProcessOrderPlace
 */
class ProcessOrderPlaceTest extends \PHPUnit\Framework\TestCase
{
    const ACCOUNT_VALUE = 200;

    /**
     * @var ProcessOrderPlace
     */
    private $orderPlace;

    /**
     * @var Repository|MockObject
     */
    private $accountRepository;

    /**
     * @var GiftCardOrderRepository|MockObject
     */
    private $giftCardOrderRepository;

    /**
     * @var GiftCardAccountTransactionProcessor|MockObject
     */
    private $giftCardAccountTransactionProcessor;

    /**
     * @var DataObject
     */
    private $event;

    /**
     * @var Observer
     */
    private $observer;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->accountRepository = $this->createPartialMock(
            Repository::class,
            ['getById', 'save']
        );
        $this->giftCardOrderRepository = $this->createPartialMock(
            GiftCardOrderRepository::class,
            ['getEmptyOrderModel']
        );
        $this->giftCardAccountTransactionProcessor = $this->createPartialMock(
            GiftCardAccountTransactionProcessor::class,
            ['startTransaction']
        );
        $this->orderPlace = $objectManager->getObject(
            ProcessOrderPlace::class,
            [
                'accountRepository' => $this->accountRepository,
                'gCardOrderRepository' => $this->giftCardOrderRepository,
                'giftCardAccountTransactionProcessor' => $this->giftCardAccountTransactionProcessor
            ]
        );

        $this->event = new DataObject();
        $this->observer = new Observer(['event' => $this->event]);
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute($giftCards, $giftCardsAmount, $accountsCount, $startTransaction)
    {
        $quote = $this->createPartialMock(
            Quote::class,
            ['isVirtual', 'getShippingAddress']
        );
        $address = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getCustomAttributesCodes']
        );
        $address->expects($this->any())->method('getCustomAttributesCodes')->willReturn([]);
        $address->addData([
            'am_gift_cards' => $giftCards,
            'base_am_gift_cards_amount' => $giftCardsAmount,
            'am_gift_cards_amount' => $giftCardsAmount
        ]);

        $quote->expects($this->any())->method('isVirtual')->willReturn(false);
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($address);
        $this->event->setQuote($quote);

        $order = $this->getOrderWithExtension();
        $this->event->setOrder($order);

        $gCardOrder = $this->createPartialMock(
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order::class,
            []
        );
        $this->giftCardOrderRepository->expects($this->atLeastOnce())->method('getEmptyOrderModel')
            ->willReturn($gCardOrder);
        $this->giftCardAccountTransactionProcessor->expects($this->exactly(count($giftCards)))
            ->method('startTransaction')
            ->willReturn($startTransaction);
        $account = $this->createPartialMock(Account::class, []);
        $account->setCurrentValue(self::ACCOUNT_VALUE);
        $this->accountRepository->expects($this->exactly($accountsCount))->method('getById')->willReturn($account);

        if (!$startTransaction) {
            $this->expectExceptionObject(new PaymentException(__('Gift Card processing error: %1', 'TST_123_AB01')));
        }

        $this->orderPlace->execute($this->observer);
        $this->assertEquals(count($gCardOrder->getAppliedAccounts()), $accountsCount);
        $this->assertEquals($giftCardsAmount, $gCardOrder->getBaseGiftAmount());
    }

    /**
     * @return Order|MockObject
     */
    protected function getOrderWithExtension()
    {
        $extensionAttributes = $this->createPartialMock(
            OrderExtension::class,
            ['setAmGiftcardOrder']
        );
        $order = $this->createPartialMock(Order::class, ['getExtensionAttributes', 'setExtensionAttributes']);
        $order->expects($this->any())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        return $order;
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                [
                    [
                        GiftCardCartProcessor::GIFT_CARD_ID => 1,
                        GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT => 50,
                        GiftCardCartProcessor::GIFT_CARD_AMOUNT => 50,
                        GiftCardCartProcessor::GIFT_CARD_CODE => 'TST_123_AB01'
                    ],
                    [
                        GiftCardCartProcessor::GIFT_CARD_ID => 2,
                        GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT => 100,
                        GiftCardCartProcessor::GIFT_CARD_AMOUNT => 100,
                        GiftCardCartProcessor::GIFT_CARD_CODE => 'TST_123_AB02'
                    ]
                ],
                150,
                2,
                true
            ],
            [[], 0, 0, true],
            [
                [
                    [
                        GiftCardCartProcessor::GIFT_CARD_ID => 1,
                        GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT => 50,
                        GiftCardCartProcessor::GIFT_CARD_AMOUNT => 50,
                        GiftCardCartProcessor::GIFT_CARD_CODE => 'TST_123_AB01'
                    ]
                ],
                50,
                1,
                false
            ]
        ];
    }
}
