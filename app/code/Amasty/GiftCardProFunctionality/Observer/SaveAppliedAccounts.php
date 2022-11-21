<?php
declare(strict_types=1);

namespace Amasty\GiftCardProFunctionality\Observer;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Amasty\GiftCardProFunctionality\Model\GiftCardAccount\Usage\Checker;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveAppliedAccounts implements ObserverInterface
{
    /**
     * @var Checker
     */
    private $usageChecker;

    public function __construct(Checker $usageChecker)
    {
        $this->usageChecker = $usageChecker;
    }

    public function execute(Observer $observer)
    {
        /** @var GiftCardAccountInterface $account */
        $account = $observer->getEvent()->getAccount();

        if ($this->usageChecker->canBeSetInUsed($account)) {
            $account->setStatus(AccountStatus::STATUS_USED);
        }
    }
}
