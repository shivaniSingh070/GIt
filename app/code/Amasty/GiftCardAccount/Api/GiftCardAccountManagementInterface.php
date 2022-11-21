<?php

namespace Amasty\GiftCardAccount\Api;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @api
 */
interface GiftCardAccountManagementInterface
{
    /**
     * Remove GiftCard Account entity
     *
     * @param string|int $cartId
     * @param string $giftCardCode
     *
     * @throws CouldNotDeleteException
     * @return string
     */
    public function removeGiftCardFromCart($cartId, string $giftCardCode): string;

    /**
     * Add gift card to the cart.
     *
     * @param string|int $cartId
     * @param string $giftCardCode
     *
     * @throws CouldNotSaveException
     * @return string
     * @deprecated
     */
    public function applyGiftCardToCart($cartId, string $giftCardCode): string;

    /**
     * Add gift card to the cart.
     *
     * @param string|int $cartId
     * @param string $giftCardCode
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface
     * @throws CouldNotSaveException
     */
    public function applyGiftCardAccountToCart($cartId, string $giftCardCode): GiftCardAccountResponseInterface;
}
