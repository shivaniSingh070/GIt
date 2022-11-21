<?php

/**
 * @author : AA
 * @description : Trigger after add to cart to get item id of last added product.
 * @date : 26.07.2019
 */

namespace Pixelmechanics\Engelsrufer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Getquote implements ObserverInterface {

    /**
     * @var \\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \\Session\SessionManagerInterface
     */
    protected $_coreSession;

    public function __construct(
    \Magento\Checkout\Model\Session $checkoutSession, \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_coreSession = $coreSession;
    }

    public function execute(Observer $observer) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getDataByKey('product');
       // price will not show for group products
        if($product->getTypeId() != "grouped"):
            /** @var \Magento\Quote\Model\Quote\Item $item */
            $item = $this->_checkoutSession->getQuote()->getItemByProduct($product);

            // start coresession to set price of last added product
            $this->_coreSession->start();
            $this->_coreSession->setlastItemPrice($item->getPriceInclTax());
        endif;
    }

}
