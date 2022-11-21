<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Command\Redeem;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Command\CommandInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account as AccountResource;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Exception\LocalizedException;

class Command implements CommandInterface
{
    /**
     * @var ProcessorPool
     */
    private $processorPool;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var AccountResource
     */
    private $accountResource;

    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var GiftCardAccountInterface
     */
    private $account;

    /**
     * @var string
     */
    private $processorKey;

    /**
     * @var int
     */
    private $customerId;

    /**
     * @var float|null
     */
    private $amount;

    public function __construct(
        ProcessorPool $processorPool,
        Repository $accountRepository,
        AccountResource $accountResource,
        ValidatorFactory $validatorFactory,
        GiftCardAccountInterface $account,
        string $processorKey,
        int $customerId,
        float $amount = null
    ) {
        $this->processorPool = $processorPool;
        $this->accountRepository = $accountRepository;
        $this->accountResource = $accountResource;
        $this->validatorFactory = $validatorFactory;
        $this->account = $account;
        $this->processorKey = $processorKey;
        $this->customerId = $customerId;
        $this->amount = $amount;
    }

    /**
     * @return GiftCardAccountInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function execute(): GiftCardAccountInterface
    {
        $validator = $this->validatorFactory->create(['validatorKey' => $this->processorKey]);
        if (!$validator->isValid($this->account)) {
            $errors = $validator->getMessages();
            throw new LocalizedException($errors[0]);
        }

        try {
            $this->accountResource->beginTransaction();
            $amountToRedeem = $this->amount == null ? $this->account->getCurrentValue() : $this->amount;
            $processor = $this->processorPool->get($this->processorKey);

            $processor->execute($this->account, $this->customerId, $amountToRedeem);
            $currenValue = (float)($this->account->getCurrentValue() - $amountToRedeem);
            $this->account
                ->setCurrentValue($currenValue)
                ->setStatus($currenValue > 0 ? AccountStatus::STATUS_ACTIVE : AccountStatus::STATUS_REDEEMED);
            $this->accountRepository->save($this->account);
            $this->accountResource->commit();
        } catch (\Exception $exception) {
            $this->accountResource->rollBack();
            throw $exception;
        }

        return $this->account;
    }
}
