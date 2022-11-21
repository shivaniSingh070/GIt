<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\Validator;

use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Quote\Model\Quote\Item;

class Discount implements \Zend_Validate_Interface
{
    /**
     * @var []
     */
    protected $messages;

    /**
     * Define if we can apply discount to current item
     *
     * @param Item $item
     * @return bool
     */
    public function isValid($item)
    {
        if (GiftCard::TYPE_AMGIFTCARD == $item->getProductType()) {
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
