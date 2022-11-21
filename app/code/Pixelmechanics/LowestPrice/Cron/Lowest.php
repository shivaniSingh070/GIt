<?php

namespace Pixelmechanics\LowestPrice\Cron;

class Lowest
{
    protected $lowestFactory;
    protected $collectionFactory;
    protected $scopeConfig;
    protected $storeManager;
    protected $rule;


    public function __construct(
        \Pixelmechanics\LowestPrice\Model\LowestFactory $lowestFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory ,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\Rule $rule
    )
    {
        $this->lowestFactory = $lowestFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->rule = $rule ; 
       
    

    }
    public function execute()
    {
        

        $isModuleEnable = $this->scopeConfig->getValue("lowest_price/general/enable",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getStoreId());
        if($isModuleEnable) {

            $productCollection = $this->collectionFactory->create()->addAttributeToSelect('*');
            $model = $this->lowestFactory->create();
            $modelCollection = $model->getCollection();

            foreach ($productCollection as $product) {

                $regularPrice = $product->getPrice();
                $finalPrice = $product->getFinalPrice();
                $catalogPrice = $this->rule->calcProductPriceRule($product, $regularPrice);
                $entityId = $product->getEntityId();
                $currentDate = date("Y-m-d");
                $savedProductDetail = $model->getCollection()
                    ->addFieldToFilter('product_id', array('eq' => $entityId))
                    ->addFieldToFilter('date', array('eq' => $currentDate))->getFirstItem()->debug();

                $lowestPrice = ($finalPrice <= $regularPrice) ? $finalPrice : $regularPrice;
                $currentLowestPrice = ($lowestPrice < $catalogPrice) ? $lowestPrice : $catalogPrice;
                // print_r($entityId. "hi".$catalogPrice . PHP_EOL);
                // print_r($entityId. "".$currentLowestPrice.PHP_EOL);
                if (!$savedProductDetail) {

                    $data = [
                        'product_id' => $entityId,
                        'date' => $currentDate,
                        'lowest_total_price' => $currentLowestPrice
                    ];
                   

                    $model->setData($data)->save();
                
                }
                elseif ($savedProductDetail['id']) {
                        
                    $model = $this->lowestFactory->create();

                    $id = $savedProductDetail['id'];
                    
                    $model->load($id);
                    
                    $model->setLowestTotalPrice($currentLowestPrice);

                    $model->save() ; 
                        
                          
                }
                

            } 
        //   die;
            return $this;

        }
    }
    


}