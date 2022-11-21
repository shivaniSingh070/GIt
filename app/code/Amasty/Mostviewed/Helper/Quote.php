<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Quote
 * @package Amasty\Mostviewed\Helper
 */
class Quote extends AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $session
    ) {
        parent::__construct($context);
        $this->session = $session;
    }

    /**
     * @return array
     */
    public function getCartProductIds()
    {
        $ids = [];
        $itemsCollection = $this->session->getQuote()->getItemsCollection();
        foreach ($itemsCollection as $item) {
            $product = $item->getProduct();
            if ($product) {
                $ids[] = $product->getId();
            }
        }

        return $ids;
    }

    /**
     * @return null|\Magento\Catalog\Model\Product
     */
    public function getLastAddedProductInCart()
    {
        $items = $this->session->getQuote()->getAllVisibleItems();
        if (!empty($items)) {
            $result = array_reverse($items);
            $product = array_key_exists(0, $result) ? $result[0]->getProduct() : null;
        } else {
            $product = null;
        }

        return $product;
    }
}
