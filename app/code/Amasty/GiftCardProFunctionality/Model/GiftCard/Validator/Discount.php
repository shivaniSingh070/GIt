<?php

declare(strict_types=1);

namespace Amasty\GiftCardProFunctionality\Model\GiftCard\Validator;

use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCardProFunctionality\Model\ConfigProvider;
use Magento\Quote\Model\Quote\Item;

class Discount implements \Zend_Validate_Interface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * Define if we can apply discount to current item
     *
     * @param Item $item
     * @return bool
     */
    public function isValid($item)
    {
        if (GiftCard::TYPE_AMGIFTCARD == $item->getProductType()
            && !$this->configProvider->isCartPriceRuleForAmGiftCard()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return [];
    }
}
