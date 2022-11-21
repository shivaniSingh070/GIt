<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\AccountTransaction;
use Magento\Framework\Stdlib\DateTime\DateTime;

class GiftCardAccountTransactionProcessor
{
    /**
     * @var AccountTransaction
     */
    private $accountTransaction;

    /**
     * @var DateTime
     */
    private $datetime;

    public function __construct(
        AccountTransaction $accountTransaction,
        DateTime $datetime
    ) {
        $this->accountTransaction = $accountTransaction;
        $this->datetime = $datetime;
    }

    public function startTransaction(GiftCardAccountInterface $account): bool
    {
        $transaction = [
            'account_id' => (int)$account->getAccountId(),
            'started_in' => $this->datetime->gmtDate()
        ];

        try {
            $connection = $this->accountTransaction->getConnection();
            $connection->insert($this->accountTransaction->getMainTable(), $transaction);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function completeTransaction(GiftCardAccountInterface $account): void
    {
        $connection = $this->accountTransaction->getConnection();
        $connection->delete(
            $this->accountTransaction->getMainTable(),
            ['account_id = ?' => (int)$account->getAccountId()]
        );
    }

    public function clearExpiredTransaction(int $period = 1): void
    {
        $connection = $this->accountTransaction->getConnection();
        $connection->delete(
            $this->accountTransaction->getMainTable(),
            ['started_in <= ?' => $this->datetime->gmtDate(null, '-' . $period . ' minutes')]
        );
    }
}
