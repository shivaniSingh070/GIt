<?php
/**
 * Overided mageplaze class to filter best seller products by price > 0, having image and enabled available later in all collections.
 * by Anoop Singh
 * See trello - https://trello.com/c/gxk6SgNq/120-044handling-von-verf%C3%BCgbarkeit-availability-handling
 */
namespace Pixelmechanics\ProductFilter\Block\Product;

class BestSellerProducts extends \Mageplaza\Productslider\Block\BestSellerProducts
{
    public function getProductCollection()
    {
        $todayDate = $this->_timezone->date()->format('Y-m-d H:i:s');
        $backDate = strtotime('-7 day', strtotime($todayDate));
        $backDate = date('Y-m-d h:i:s', $backDate);
        
        $productIds = [];
        $bestSellers = $this->_bestSellersCollectionFactory->create()
                             ->setPeriod('day')
                             ->setDateRange($backDate, $todayDate);
       
       
        foreach ($bestSellers as $product) {
            $productIds[] = $product->getProductId();
        }
         

        $collection = $this->_productCollectionFactory->create()->addIdFilter($productIds);
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*');

        /******added below code to filter collection by price,image and available later attribute by anoop****/
        $firstCollection = clone $collection;
        $secondCollection = clone $collection;
        $finalCollection = $collection;

        $simpleCollection = $firstCollection
            ->addAttributeToFilter('price', array('gt' => 0))
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'image','neq' => 'no_selection'),
                    array('attribute' => 'small_image','neq' => 'no_selection'),
                    array('attribute' => 'thumbnail','neq' => 'no_selection'),
                )
            )
            ->addAttributeToFilter('available_later', 1);

        $configCollection = $secondCollection
                            ->addAttributeToFilter(array(array('attribute' => 'type_id','neq' => 'simple')));
        $merged_ids = array_merge($simpleCollection->getAllIds(), $configCollection->getAllIds());

        $resultedCollection = $finalCollection
                                ->addAttributeToFilter('entity_id', array('in' => $merged_ids))
                                ->addStoreFilter($this->getStoreId())->setPageSize($this->getProductsCount());
        return $resultedCollection;
    }
}
