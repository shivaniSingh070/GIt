<?php
/**
 * Overided mageplaze class to filter new products by price > 0, having image and enabled available later in all collections.
 * by Anoop Singh
 * See trello - https://trello.com/c/gxk6SgNq/120-044handling-von-verf%C3%BCgbarkeit-availability-handling
 */
namespace Pixelmechanics\ProductFilter\Block\Product;

use Zend_Db_Expr;

class NewProducts extends \Mageplaza\Productslider\Block\NewProducts
{
    public function getProductCollection()
    {
        $visibleProducts = $this->_catalogProductVisibility->getVisibleInCatalogIds();
        $collection = $this->_productCollectionFactory->create()->setVisibility($visibleProducts);
        $collection = $this->_addProductAttributesAndPrices($collection)
                            ->addAttributeToFilter(
                                'news_from_date',
                                ['date' => true, 'to' => $this->getEndOfDayDate()],
                                'left'
                            )
                            ->addAttributeToFilter(
                                'news_to_date',
                                [
                                    'or' => [
                                        0 => ['date' => true, 'from' => $this->getStartOfDayDate()],
                                        1 => ['is' => new Zend_Db_Expr('null')],
                                    ]
                                ],
                                'left'
                            );
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
                                ->addAttributeToSort(
                                    'news_from_date',
                                    'desc'
                                )
                                ->addStoreFilter($this->getStoreId())
                                ->setPageSize($this->getProductsCount());

        return $resultedCollection;
    }
}
