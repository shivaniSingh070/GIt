<?php

namespace Amasty\GiftCardAccount\Api;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

interface GiftCardAccountCommandManagementInterface
{
    /**
     * Redeem gift card to Am Store Credit
     *
     * @param string $giftCardCode
     * @param int $customerId
     * @param float|null $amount
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function redeemToAmStoreCredit(
        string $giftCardCode,
        int $customerId,
        float $amount = null
    ): GiftCardAccountInterface;
}
