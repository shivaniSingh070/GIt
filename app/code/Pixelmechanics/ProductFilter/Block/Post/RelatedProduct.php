<?php
/**
 * Overided mageplaza class to filter blog's products by price > 0, having image and enabled available later in all collections.
 * by Anoop Singh
 * See trello - https://trello.com/c/gxk6SgNq/120-044handling-von-verf%C3%BCgbarkeit-availability-handling
 */
namespace Pixelmechanics\ProductFilter\Block\Post;

class RelatedProduct extends \Mageplaza\Blog\Block\Post\RelatedProduct
{
    public function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $postId = $this->getRequest()->getParam('id');
            $collection = $this->_productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addStoreFilter();

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

            $configCollection = $secondCollection->addAttributeToFilter(array(array('attribute' => 'type_id','neq' => 'simple')));

            $merged_ids = array_merge($simpleCollection->getAllIds(), $configCollection->getAllIds());

            $resultedCollection = $finalCollection->addAttributeToFilter('entity_id', array('in' => $merged_ids));

            $resultedCollection->getSelect()
                ->join(
                    ['product_post' => $resultedCollection->getTable('mageplaza_blog_post_product')],
                    "e.entity_id = product_post.entity_id"
                )
                ->where('product_post.post_id = ' . $postId)
                ->order('product_post.position ASC')
                ->limit((int)$this->helper->getBlogConfig('product_post/post_detail/product_limit') ?: self::LIMIT);

            $this->_productCollection = $resultedCollection;
        }

        return $this->_productCollection;
    }
}
