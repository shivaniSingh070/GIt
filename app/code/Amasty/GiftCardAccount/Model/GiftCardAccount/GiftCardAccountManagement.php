<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;
use Amasty\GiftCardAccount\Api\GiftCardAccountManagementInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\CartAction\Response\Builder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;

class GiftCardAccountManagement implements GiftCardAccountManagementInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var GiftCardCartProcessor
     */
    private $gCardCartProcessor;

    /**
     * @var Builder
     */
    private $responseBuilder;

    public function __construct(
        Repository $accountRepository,
        CartRepositoryInterface $quoteRepository,
        GiftCardCartProcessor $gCardCartProcessor,
        Builder $responseBuilder
    ) {
        $this->accountRepository = $accountRepository;
        $this->quoteRepository = $quoteRepository;
        $this->gCardCartProcessor = $gCardCartProcessor;
        $this->responseBuilder = $responseBuilder;
    }

    public function removeGiftCardFromCart($cartId, string $giftCardCode): string
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$quote->getItemsCount()) {
            throw new CouldNotDeleteException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }

        try {
            $giftCard = $this->accountRepository->getByCode($giftCardCode);
            $this->gCardCartProcessor->removeFromCart($giftCard, $quote);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__("The gift card couldn't be deleted from the quote."));
        }

        return $giftCardCode;
    }

    public function applyGiftCardToCart($cartId, string $giftCardCode): string
    {
        $response = $this->applyGiftCardAccountToCart($cartId, $giftCardCode);

        return $response->getAccount()->getCodeModel()->getCode();
    }

    public function applyGiftCardAccountToCart(
        $cartId,
        string $giftCardCode
    ): GiftCardAccountResponseInterface {
        $giftCardCode = trim($giftCardCode);

        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$quote->getItemsCount()) {
            throw new CouldNotSaveException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }

        try {
            $giftCard = $this->accountRepository->getByCode($giftCardCode);
            $this->gCardCartProcessor->applyToCart($giftCard, $quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $this->responseBuilder->build($giftCard, Builder::ADD_TO_CART);
    }
}
