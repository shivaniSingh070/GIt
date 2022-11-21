<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcart\Block;

/**
 * Class CartInfo
 * @package Tigren\Ajaxcart\Block
 */
class CartInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * CartInfo constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Cart $cart
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->_cart = $cart;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return $this->_cart->getItemsCount();
    }

    /**
     * @return float|int
     */
    public function getItemsQty()
    {
        return $this->_cart->getItemsQty();
    }

    /**
     * @return float
     */
    public function getSubTotal()
    {
        return $this->_cart->getQuote()->getSubtotal();
    }
}