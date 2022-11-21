<?php
/**
 * Overided magento class to filter related products by price > 0, having image and enabled available later in all collections.
 * by Anoop Singh
 * See trello - https://trello.com/c/gxk6SgNq/120-044handling-von-verf%C3%BCgbarkeit-availability-handling
 */
namespace Pixelmechanics\ProductFilter\Block\Product;

class Related extends \Magento\Catalog\Block\Product\ProductList\Related
{
    protected function _prepareData()
    {
        $product = $this->getProduct();
        /* @var $product \Magento\Catalog\Model\Product */

        $this->_itemCollection = $product->getRelatedProductCollection()->addAttributeToSelect(
            'required_options'
        )->setPositionOrder()->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            /*****added by anoop to remove item if price is zero or not having image or is not available later****/
            if (($product->getTypeId() == 'simple') && (!$product->getAvailableLater() || ($product->getPrice() <= 0) || (!$product->getImage() || $product->getImage() == 'no_selection'))) {
                $this->_itemCollection->removeItemByKey($product->getId());
            }
            /****end of code by anoop*******/
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }
}
