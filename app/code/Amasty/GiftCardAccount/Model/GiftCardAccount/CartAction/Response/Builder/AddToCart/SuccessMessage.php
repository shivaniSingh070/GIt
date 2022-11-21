<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\CartAction\Response\Builder\AddToCart;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\CartAction\Response\Builder\BuilderInterface;
use Magento\Framework\Message\Factory as MessageFactory;
use Magento\Framework\Message\MessageInterface;

class SuccessMessage implements BuilderInterface
{
    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @param MessageFactory $messageFactory
     */
    public function __construct(MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function build(
        GiftCardAccountInterface $account,
        GiftCardAccountResponseInterface $response
    ): void {
        $successMsg = $this->messageFactory->create(
            MessageInterface::TYPE_SUCCESS,
            __('Gift Card "%1" was added.', $account->getCodeModel()->getCode())
        );

        $response->addMessage($successMsg);
    }
}
