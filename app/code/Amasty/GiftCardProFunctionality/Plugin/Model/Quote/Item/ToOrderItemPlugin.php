<?php
declare(strict_types=1);

namespace Amasty\GiftCardProFunctionality\Plugin\Model\Quote\Item;

use Amasty\GiftCard\Model\Config\Source\Usage;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Model\Order\Item;

class ToOrderItemPlugin
{
    /**
     * @param ToOrderItem $subject
     * @param Item $orderItem
     * @param AbstractItem $quoteItem
     * @param array $data
     *
     * @return Item
     */
    public function afterConvert(
        ToOrderItem $subject,
        Item $orderItem,
        AbstractItem $quoteItem,
        array $data = []
    ): Item {
        $productOptions = $orderItem->getProductOptions();
        $product = $quoteItem->getProduct();

        if ($product->getTypeId() != GiftCard::TYPE_AMGIFTCARD) {
            return $orderItem;
        }

        $productOptions[Attributes::USAGE] = $product->getAmGiftcardUsage() ?? Usage::MULTIPLE;
        $orderItem->setProductOptions($productOptions);

        return $orderItem;
    }
}
