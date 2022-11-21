<?php
    /* Updated by NA 
    *  date 3.07.19
    *  To add the Bullet Points on the checout summery section
    */
?>
<?php

namespace Pixelmechanics\CheckoutSummary\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ProductRepository as ProductRepository;
 
class DefaultConfigProviderPlugin extends \Magento\Framework\Model\AbstractModel
{
    protected $checkoutSession;
 
    protected $_productRepository;
 
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepository $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_productRepository = $productRepository;
    }
 
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject, 
        array $result
    ) {
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $product = $this->_productRepository->getById($quoteItem->getProduct()->getId());
            $bullets = null;
            $bullets .=  '<span class="osc-product-bullet">'.$product->getResource()->getAttribute('bullet_point_1')->getFrontend()->getValue($product).'</span>';
            $bullets .=  '<span class="osc-product-bullet">'.$product->getResource()->getAttribute('bullet_point_2')->getFrontend()->getValue($product).'</span>';
            $bullets .=  '<span class="osc-product-bullet">'.$product->getResource()->getAttribute('bullet_point_3')->getFrontend()->getValue($product).'</span>';
            $bullets .=  '<span class="osc-product-bullet">'.$product->getResource()->getAttribute('bullet_point_4')->getFrontend()->getValue($product).'</span>';
            $bullets .=  '<span class="osc-product-bullet">'.$product->getResource()->getAttribute('bullet_point_5')->getFrontend()->getValue($product).'</span>';

            $result['quoteItemData'][$index]['bullet_point_1'] = $bullets;
        }
        return $result;
    }
}