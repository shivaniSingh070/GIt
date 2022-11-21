<?php
declare(strict_types=1);


namespace Amasty\GiftCard\Pricing\Price;

use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;

/**
 * Final price model
 */
class FinalPrice extends CatalogFinalPrice
{
    /**
     * @return array
     */
    public function getAmounts(): array
    {
        if ($this->product->hasOptions()) {
            return [0]; // Gift Card amount = 0 will be added to final price instead min amount
        }

        $amountsCache = [];
        foreach ($this->product->getAmGiftcardPrices() as $amount) {
            $amountsCache[] = $this->priceCurrency->convertAndRound($amount['value']);
        }
        sort($amountsCache);

        return $amountsCache;
    }

    public function getValue()
    {
        $amount = $this->getAmounts();

        return count($amount) ? array_shift($amount) : false;
    }
}
