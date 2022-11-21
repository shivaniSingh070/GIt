<?php

/* Creted Helper for module
 * @package  Pixelmechanics_Engelsrufer
 * @module   Engelsrufer
 * Used for global Catalog functions required throughtout the website
 * Created by AA 09.05.2019
 */

namespace Pixelmechanics\Engelsrufer\Helper;

use Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPrice;

class Catalog extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /*
     *  @var \Magento\Eav\Api\AttributeSetRepositoryInterface *
     */
    protected $attributeSet;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
    * @var \Magento\Catalog\Helper\Category
    */
    protected $categoryHelper;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \\Mageplaza\Blog\Model\PostFactory
     */
    protected $_postCollection;
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \\Session\SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */

    protected $productRepository;
    /**
     * [__construct description].
     *
     * @param \Magento\Framework\App\Helper\Context             $context
     * @param \Magento\Framework\ObjectManagerInterface         $objectManager
     * @param \Magento\Cms\Model\Template\FilterProvider        $filterProvider
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface  $attributeSet
     * @param \Magento\Catalog\Helper\Category                  $categoryHelper
     * @param \Magento\Catalog\Model\CategoryRepository         $categoryRepository
     * @param \Mageplaza\Blog\Model\PostFactory                 $modelPostFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Session\SessionManagerInterface   $coreSession
     * @param \Magento\Store\Model\StoreManagerInterface     $storeManager
     */


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Mageplaza\Blog\Model\PostFactory $modelPostFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->attributeSet = $attributeSet;
        $this->_filterProvider =$filterProvider;
        $this->_objectManager = $objectManager;
        $this->categoryHelper = $categoryHelper;
        $this->categoryRepository = $categoryRepository;
        $this->_postCollection = $modelPostFactory;
        $this->localeDate = $localeDate;
        $this->_coreSession = $coreSession;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * get current product
     * @return product object
     */

    public function getCurrentProduct() {
        return $this->registry->registry('current_product');
    }

     /**
     * get current category
     * @return product object
     * By AA on 22.05.2019
     */

    public function getCurrentCategory() {
        return $this->registry->registry('current_category');
    }

    /**
     * get current product
     * @return string (current product attribute ID)
     */

    public function getAttributeSetId() {
        $product = $this->getCurrentProduct();
        $attributeSetRepository = $this->attributeSet->get($product->getAttributeSetId());
        return $attributeSetRepository->getAttributeSetId();
    }

    /**
     * get attribute group name
     * @return int (attribute group ID)
     */

    public function getProductAttributeGroupId($attributeGroupName) {

        $config = $this->_objectManager->get('Magento\Catalog\Model\Config');
        $attributeID = $this->getAttributeSetId();
        return $config->getAttributeGroupId($attributeID, $attributeGroupName);
    }

    /**
    * get string (attribute value)
    * @return string
    */

    public function getWysiwygAttributeValue($attribute)
    {
        $attributeValue = $this->_filterProvider->getPageFilter()->filter(
            $attribute
        );
        return $attributeValue;
    }

    /**
    * get id (category ID
    * @return string (category URL)
    */

     public function getCategoryUrl($categoryID)
    {
        $categoryObj = $this->categoryRepository->get($categoryID);
        return $this->categoryHelper->getCategoryUrl($categoryObj);
    }

    /**
    * get blog collection from blog ID
    * @return collection
    */

   public function getBlogData($postId){
        $resultPage = $this->_postCollection->create();
        $collection = $resultPage->getCollection()
                    ->addFieldToFilter("post_id",$postId)
                    ->addFieldToFilter("enabled",1)->load();
        return $collection->getData();
    }

    /**
    * get product by id
    * @return object
    * Updated by AA on 23.07.2019
    */

    public function getProductById($pid) {
         return $this->_objectManager->create('Magento\Catalog\Model\Product')->load($pid);
    }

    /**
    * get count of configurable variant from product id By NA, Date-20may-2019 https://trello.com/c/lBLT9AAh/
    * @return count
    */

    public function getConfigAttributeValue($pid){

       $configurableProduct = $this->productRepository->getById($pid);
       $productType = $configurableProduct->getTypeId();
       if($productType == "configurable"){
            $children = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
            return count($children);
       }
       else{
           return 0;
       }
   }


   /**
   * Get the New Label for newly uploaded products
   * By NA, 22May2019
   */

   public function isProductNew($product)
   {
        $newsFromDate = $product->getNewsFromDate();
        $newsToDate = $product->getNewsToDate();
        if (!$newsFromDate && !$newsToDate) {
            return false;
        }
        return $this->localeDate->isScopeDateInInterval(
            $product->getStore(),
            $newsFromDate,
            $newsToDate
        );

   }

   /**
   * Check the special price of products to display the sale % label on products & list page By NA , 22May2019
   * @link:https://trello.com/c/DAB1vbqW/87-sale-article
   * add the date condition for sale badge, if date is in between on start date and end date
   * updated by N.A 20.01.2020
   */
   public function displayDiscountLabel($product)
    {
        $specialPrice    = $product->getSpecialPrice(); // get the special price of product

        $specialFromDate = $product->getSpecialFromDate(); //get the special from date for special price
        $specialToDate   = $product->getSpecialToDate();  //get the special to date for special price

        //check if date exist in Interval of start and end date.
        $dateInInterval = $this->localeDate->isScopeDateInInterval($product->getStoreID(), $specialFromDate, $specialToDate);
        if($product->getTypeId() == 'simple'){
            if($dateInInterval && $specialPrice){
                return "%";
            }
        } else{
            // updated the constion for the configurable product by N.A on 23.01.2020
            $configurableProduct = $this->productRepository->getById($product->getId());
            $productType = $configurableProduct->getTypeId();
                if($productType == "configurable"){
                    $childrens = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct); //get the variant of configurable product
                    foreach($childrens as $children){
                        $dateInIntervalChild = $this->localeDate->isScopeDateInInterval(
                                                    $product->getStoreID(),
                                                    $children->getSpecialFromDate(),
                                                    $children->getSpecialToDate()
                                                );
                        $childSpecialPrice= $children->getSpecialPrice();
                        //check if the current date is between of the from date and To Date
                        if($dateInIntervalChild && $childSpecialPrice){
                            return '%';
                        }
                    }

            }
        }

    }

    /**
     * get item price of last added product through session
     * Updated on 26.07.2019
    */
   public function getItemPrice()
    {
        $this->_coreSession->start();
        // Format price (add currency symbol)
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');
        $price =  $this->_coreSession->getlastItemPrice();
        return $priceHelper->currency($price, true, false);
    }

    /**
     * unset the last added item price session
     * Updated on 26.07.2019
    */

    public function unSetItemPrice()
    {
        $this->_coreSession->start();
        return $this->_coreSession->unslastItemPrice();
    }

     /**
     * get Pub media Url
     * @return string
     */

    public function getPubMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
    * Show the gift product price using the product ID for home page sliders by N.A on 31 Oct 2019
    * trello: @noshad6 [price is not displayed on main page](https://trello-attachments.s3.amazonaws.com/5c7fce608f73ec77926941d7/5c86d8e3af1f3384feaef8db/b47b7a192da36d59985f459b0de2aedf/image.png)
    */
    public function getPriceByProductId($productId)
    {

            $connection = $this->_objectManager->get('Magento\Framework\App\ResourceConnection')
                                ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
            /*get the collection from the amasty_amgiftcard_price table */
            //Update table name via constant as used in amasty
            $collection = $connection->fetchAll("SELECT * FROM ". GiftCardPrice::TABLE_NAME." where product_id=$productId");
            $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');
            $giftCardPrice = 0;
            foreach ($collection as $value) {
                $giftCardPrice = $value['value'];
            }

            return  $priceHelper->currency($giftCardPrice, true, false);

    }

}
