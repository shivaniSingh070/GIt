<?php
declare(strict_types=1);

namespace Amasty\GiftCardProFunctionality\Model\GiftCardAccount\CartAction\Response\Builder\AddToCart;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\CartAction\Response\Builder\BuilderInterface;
use Amasty\GiftCardProFunctionality\Model\GiftCardAccount\Usage\Checker;
use Magento\Framework\Message\Factory as MessageFactory;
use Magento\Framework\Message\MessageInterface;

class UsageMessage implements BuilderInterface
{
    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var Checker
     */
    private $usageChecker;

    public function __construct(
        MessageFactory $messageFactory,
        Checker $usageChecker
    ) {
        $this->messageFactory = $messageFactory;
        $this->usageChecker = $usageChecker;
    }

    public function build(
        GiftCardAccountInterface $account,
        GiftCardAccountResponseInterface $response
    ): void {
        if ($this->usageChecker->canBeSetInUsed($account)) {
            $successMsg = $this->messageFactory->create(
                MessageInterface::TYPE_WARNING,
                __('Please mind that you can apply this code only once.')
            );

            $response->addMessage($successMsg);
        }
    }
}
