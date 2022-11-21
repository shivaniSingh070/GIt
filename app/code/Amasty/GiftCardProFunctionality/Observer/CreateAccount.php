<?php
declare(strict_types=1);

namespace Amasty\GiftCardProFunctionality\Observer;

use Amasty\GiftCard\Model\Config\Source\Usage;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreateAccount implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $data = $observer->getEvent()->getAccountData();
        /** @var GiftCardAccountInterface $account */
        $account = $observer->getEvent()->getAccount();

        $account->setUsage($data->getUsage() ?? Usage::MULTIPLE);
    }
}
