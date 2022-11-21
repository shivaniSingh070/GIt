<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Api\GiftCardAccountCommandManagementInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Command\CommandFactory;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Command\Redeem\ProcessorPool;

class GiftCardAccountCommandManagement implements GiftCardAccountCommandManagementInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var CommandFactory
     */
    private $commandFactory;

    public function __construct(
        Repository $accountRepository,
        CommandFactory $commandFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->commandFactory = $commandFactory;
    }

    public function redeemToAmStoreCredit(
        string $giftCardCode,
        int $customerId,
        float $amount = null
    ): GiftCardAccountInterface {
        $account = $this->accountRepository->getByCode($giftCardCode);
        $command = $this->commandFactory->create(
            CommandFactory::REDEEM_COMMAND,
            [
                'account' => $account,
                'processorKey' => ProcessorPool::AM_STORECREDIT,
                'customerId' => $customerId,
                'amount' => $amount
            ]
        );

        return $command->execute();
    }
}
