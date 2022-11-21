<?php

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Command\Redeem;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

/**
 * Redeem Processor Interface
 */
interface ProcessorInterface
{
    /**
     * Execute redeem process
     *
     * @param GiftCardAccountInterface $account
     * @param int $customerId
     * @param float $amount
     * @return void
     */
    public function execute(GiftCardAccountInterface $account, int $customerId, float $amount);
}
