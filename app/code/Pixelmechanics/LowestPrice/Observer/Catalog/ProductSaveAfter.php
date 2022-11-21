<?php
/**
 * Created an Observer that run after product save in admin
 * @author - Shivani
 * @date - 15.09.2022
 * @link - https://pixelmechanics.atlassian.net/browse/ENRUSM-46
 */

declare(strict_types=1);

namespace Pixelmechanics\LowestPrice\Observer\Catalog;

class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{

    protected $scopeConfig;
    protected $lowestFactory;
    protected $collectionFactory;
    protected $storeManager;
    private $originalPrice;
    private $productId;
    private $currentLowestPrice;
    private $notSavedLowestPrice;
    /**
     * Execute observer
     *
     */

    public function __construct(
        \Pixelmechanics\LowestPrice\Model\LowestFactory $lowestFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->lowestFactory = $lowestFactory;
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->originalPrice = null;
        $this->productId     = null;
        $this->currentLowestPrice =null;
        $this->notSavedLowestPrice =null;

    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {


        #to check module is enable or disable

        $isModuleEnable = $this->scopeConfig->getValue("lowest_price/general/enable",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore()->getStoreId());


        $_product      = $observer->getProduct();          // getting the product details
        $this->productId     = $_product->getId();              // product id
        $this->originalPrice = $_product->getPrice();           // Original price of product
        $specialPrice  = $_product->getSpecialPrice();    // special price on product
        $lowestPrice   = $_product->getLowestPrice();     // lowest price on product
        $finalPrice    = $this->originalPrice - $specialPrice;
        // var_dump($_product->getFinalPrice());die;
        $model = $this->lowestFactory->create();

        # fetching data from catalog_product_lowest_total_price_log table

        $currentDate = date("Y-m-d");

        $savedProductDetail = $this->lowestFactory->create()->getCollection()
            ->addFieldToFilter('product_id',array('eq'=>  $this->productId))
            ->addFieldToFilter('date',array('eq'=> $currentDate))->getFirstItem()->debug();

        try {
            if ($isModuleEnable)
            {
                if ($specialPrice && ($finalPrice < $this->originalPrice)) {

                    # This condition check if product special price exist AND your final price is less than original price
                    # ONLY then this Block will execute

                    if ($savedProductDetail) {
                        print_r("valid");
                        $id = $savedProductDetail['id'];
                        $savedLowestPrice = $savedProductDetail['lowest_total_price'];
                        $lowestPrice = ($specialPrice < $lowestPrice) ? $specialPrice : $lowestPrice;
                        $this->currentLowestPrice = ($savedLowestPrice < $lowestPrice) ? $savedLowestPrice : $lowestPrice;
                        $this->notSavedLowestPrice  =  ($this->currentLowestPrice < $specialPrice) ?  $this->currentLowestPrice : $specialPrice;
                        // print_r($savedLowestPrice);die;
                        if ($id && $lowestPrice !== NULL) {
                            
                            $this->updateInCatalogLowestPriceTable();

                        } 
                        else {
                            
                            $this->updateIfLowestPriceNotGiven();

                        }

                    } else {
                       
                        $this->insertInCatalogLowestPriceTable();


                    }
                } else {
                    # This block execute if product is not on sale
                    if (!$savedProductDetail) {

                        $this->insertInCatalogLowestPriceTable();

                    } else {
                        $id = $savedProductDetail['id'];
                        $savedLowestPrice = $savedProductDetail['lowest_total_price'];
                        $currentOriginalPrice = ($savedLowestPrice < $this->originalPrice) ? $savedLowestPrice : $this->originalPrice;

                        if ($id) {
                            $model = $this->lowestFactory->create();
                            $model->load($id);
                            $model->setLowestTotalPrice($currentOriginalPrice);
                            $model->save();
                        }
                    }

                }


            }
        }
        catch(\Exception $e){

            echo('Message: ' .$e->getMessage());
        }
        return $observer;
    }
    public function insertInCatalogLowestPriceTable()
    {

        $currentDate = date("Y-m-d");
        $originalPrice = $this->originalPrice;
        $productId     = $this->productId;

        $model = $this->lowestFactory->create();

        $data = [
                'product_id' => $productId,
                'date'      => $currentDate,
                'lowest_total_price' => $originalPrice
            ];
            // print_r("SFdsdf");die;
        $model->setData($data)->save();
         return true;
    }
    public function updateInCatalogLowestPriceTable()
    {
        $currentDate = date("Y-m-d");
        $productId     = $this->productId;
        $savedProductDetail = $this->lowestFactory->create()->getCollection()
            ->addFieldToFilter('product_id',array('eq'=>  $productId))
            ->addFieldToFilter('date',array('eq'=> $currentDate))->getFirstItem()->debug();
        $id =   $savedProductDetail['id'];
        try {
            $model = $this->lowestFactory->create();
            $model->load($id);
            $model->setLowestTotalPrice($this->currentLowestPrice);
            $model->save();
            // print_r("SFdsxvcxcvdf");die;
        }
        catch(\Exception $e){

            echo('Message: ' .$e->getMessage());
        }
         return true ;
    }
    public function updateIfLowestPriceNotGiven()
    {
        $currentDate = date("Y-m-d");
        $productId     = $this->productId;
        $savedProductDetail = $this->lowestFactory->create()->getCollection()
            ->addFieldToFilter('product_id',array('eq'=>  $productId))
            ->addFieldToFilter('date',array('eq'=> $currentDate))->getFirstItem()->debug();
        $id =   $savedProductDetail['id'];
        try {
            $model = $this->lowestFactory->create();
            $model->load($id);
            $model->setLowestTotalPrice($this->notSavedLowestPrice);
            print_r($this->notSavedLowestPrice);die;

            $model->save();
        }
        catch(\Exception $e){

            echo('Message: ' .$e->getMessage());
        }
         return true ;
    }

}