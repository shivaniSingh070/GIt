<?php

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\CartAction\Response\Builder;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;

interface BuilderInterface
{
    /**
     * @param GiftCardAccountInterface $account
     * @param GiftCardAccountResponseInterface $response
     * @return void
     */
    public function build(GiftCardAccountInterface $account, GiftCardAccountResponseInterface $response): void;
}
