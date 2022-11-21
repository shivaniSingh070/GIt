<?php

namespace Amasty\GiftCardAccount\Api\Data;

use Magento\Framework\Message\MessageInterface;

/**
 * Gift Card account response interface.
 * Used for messages about transactions with the account.
 * @api
 */
interface GiftCardAccountResponseInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ACCOUNT = 'account';
    const MESSAGES = 'messages';
    /**#@-*/

    /**
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function getAccount(): GiftCardAccountInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface $account
     * @return $this
     */
    public function setAccount(GiftCardAccountInterface $account): GiftCardAccountResponseInterface;

    /**
     * @return \Magento\Framework\Message\MessageInterface[]|null
     */
    public function getMessages(): ?array;

    /**
     * @param \Magento\Framework\Message\MessageInterface[] $messages
     * @return $this
     */
    public function setMessages(array $messages): GiftCardAccountResponseInterface;

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function addMessage(MessageInterface $message): void;
}
