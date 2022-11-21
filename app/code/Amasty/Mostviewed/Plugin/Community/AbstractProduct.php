<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Plugin\Community;

use Amasty\Mostviewed\Model\OptionSource\BlockPosition;

/**
 * Class AbstractProduct
 * @package Amasty\Mostviewed\Plugin\Community
 */
abstract class AbstractProduct
{
    const OBSERVED = 'ammostviwed_obesrved';

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var mixed
     */
    private $currentProduct;

    /**
     * @var \Amasty\Mostviewed\Helper\Quote
     */
    private $quoteHelper;

    /**
     * @var \Amasty\Mostviewed\Model\ProductProvider
     */
    private $productProvider;

    public function __construct(
        \Amasty\Mostviewed\Model\ProductProvider $productProvider,
        \Magento\Framework\Registry $registry,
        \Amasty\Mostviewed\Helper\Quote $quoteHelper
    ) {
        $this->registry = $registry;
        $this->currentProduct = $this->registry->registry('current_product');
        $this->quoteHelper = $quoteHelper;
        $this->productProvider = $productProvider;
    }

    /**
     * @param string $type
     * @param $collection
     * @param $block
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection
     */
    protected function prepareCollection($type, $collection, $block)
    {
        $excludedProducts = [];
        if ($type === BlockPosition::CART_INTO_CROSSSEL) {
            $excludedProducts = $this->quoteHelper->getCartProductIds();
            $this->setCurrentProductForCart();
        }

        $product = $this->getProduct();
        if ($product && $type) {
            $key = $type . $product->getId();
            if (!isset($this->cache[$key])) {
                $excludedProducts[] = $product->getId();
                $this->cache[$key] = $this->productProvider->modifyCollection(
                    $type,
                    $product,
                    $collection,
                    $excludedProducts,
                    $block
                );
            }

            $collection = $this->cache[$key];
        }

        return $collection;
    }

    /**
     * @return null|\Magento\Catalog\Model\Product
     */
    private function getProduct()
    {
        return $this->currentProduct;
    }

    /**
     * @return $this
     */
    private function setCurrentProductForCart()
    {
        $this->currentProduct = $this->quoteHelper->getLastAddedProductInCart();

        return $this;
    }
}
