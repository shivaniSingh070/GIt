<?php
namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Command;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

interface CommandInterface
{
    /**
     * @return GiftCardAccountInterface
     */
    public function execute(): GiftCardAccountInterface;
}
