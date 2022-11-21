<?php
declare(strict_types=1);

namespace Amasty\GiftCardProFunctionality\Model\GiftCardAccount\Usage;

use Amasty\GiftCard\Model\Config\Source\Usage;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;

class Checker
{
    /**
     * @param GiftCardAccountInterface $account
     * @return bool
     */
    public function isSingleUsed(GiftCardAccountInterface $account): bool
    {
        return $account->getUsage() == Usage::SINGLE
            && !in_array($account->getStatus(), [AccountStatus::STATUS_ACTIVE]);
    }

    /**
     * @param GiftCardAccountInterface $account
     * @return bool
     */
    public function canBeSetInUsed(GiftCardAccountInterface $account): bool
    {
        return $account->getUsage() == Usage::SINGLE
            && in_array($account->getStatus(), [AccountStatus::STATUS_ACTIVE]);
    }
}
