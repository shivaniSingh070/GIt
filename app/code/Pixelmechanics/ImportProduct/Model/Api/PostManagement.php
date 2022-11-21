<?php

/**
 * @author : AA
 * @template-Version : Magento 2.3.1
 * @description : import Orders from CSV file
 * @date : 5.08.2019
 * @Trello: https://trello.com/c/pk8egBYL
 */

namespace Pixelmechanics\ImportProduct\Model\Api;

class PostManagement implements \Pixelmechanics\ImportProduct\Api\PostManagementInterface {

    protected $_testApiFactory;

    /**
     * @var \Pixelmechanics\ExportOrder\Logger\Logger
     */
    protected $_logger;
    
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;
    
    /**
     * @var Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
    \Pixelmechanics\ImportProduct\Logger\Logger $logger, 
    \Magento\Framework\File\Csv $csv, 
    \Magento\Framework\Filesystem\DirectoryList $dir,
    \Magento\Framework\ObjectManagerInterface $objectManager,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    
    ) {
        $this->_logger = $logger;
        $this->csv = $csv;
        $this->_dir = $dir;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
    }   

    public function getPost() {

        // Soll im Live vermieden werden, da ungeschützt
        // https://trello.com/c/ds9h7Vo1/34-za-api-anbindung-navision-navision-entwicklung-teil-2a-import-produkt-qty#action-5dc41b34defe695140ec1d6f
        if(ENVIRONMENT == "production") {
            print("Bitte nur über die Shell aufrufen: bin/magento navision:importQty");
            return;
        }

        $this->getFromNAVISION();
        // $this->importRows();    
    }

    /**
     * Call the SOAP-API and retrieve the inventory-data for products from the B2C-Stock.
     * PM LB, 06.11.2019
     * @link https://trello.com/c/ds9h7Vo1/34-za-api-anbindung-navision-navision-entwicklung-teil-2a-import-produkt-qty
     * @link https://trello.com/c/pk8egBYL/9-api-09import-product-qty-from-navision-to-magento
     **/
    public function getFromNAVISION() {
        
        include_once(MAGE_ROOT."/pm_navision_helper.php");

        $result = $this->getNavisionCredentials();
        if ($result===false) {
            return false;
        }
	   
        $this->navHelper = new \Nav($this->navision_url, "Artikel", $this->navision_login, $this->navision_pwd, $this->navision_soapAction);

        /**
         * In production, we want to make as few calls as possible
         * without returning too many Results (may cause a timeout)
         */
            $setSize = "125";

            if (ENVIRONMENT!=="production") {
                $setSize = "5"; //Smaller set Size for Debugging
            }

        $productQtys = $this->navHelper->getAllProductQuantities("_B2C", "5");

        echo $productQtys;

        if ($productQtys===false || empty($productQtys)) {
            preprint($this->navHelper->error, __FILE__.__LINE__); 
            preprint($productQtys, __FILE__.__LINE__);
            die();
            return false;
        }

        $success = 0;
        $failed  = 0;
        $prodCount = count($productQtys);
        preprint("Api call returned $prodCount products.");
        foreach ($productQtys as $prod) {
            $sku = $prod["sku"];
            $qty = $prod["qty"];
            $is_in_stock = 1;
            if ($qty<=0) {
                $is_in_stock = 0;
            }

            try {

                $_product = $this->_objectManager->create('\Magento\Catalog\Model\Product')->loadByAttribute('sku',  $sku);
                // check if product exist then update product qty and stock status
                if (!$_product) {
                    $failed++;
                    preprint("Produkt '$sku' nicht gefunden.", __FILE__.__LINE__);
                    continue;
                }

                $stockData = array(
                    'is_in_stock' => $is_in_stock, // Stock Availability of product
                    'qty' => $qty
                );
                $_product->setStockData($stockData);

                // save product
                    $_product->save();
                    $success++;
                    $msg = "product with SKU $sku is updated with qty $qty";
                    $this->_logger->info($msg);
            }
            catch (\Exception $e) {
                $failed++;
                $msg = "Produkt '$sku' nicht gefunden oder Fehler beim Laden.";
                $this->_logger->error($msg);
                return;
            }

        } // foreach productQtys
        
        $msg = "$success erfolgreich importiert, $failed Fehler!";
        $this->_logger->info($msg);
        preprint($msg, __FILE__.__LINE__); die();
    }


/*
    public function importRows() {
        foreach ($this->rows 1 ... ) {

        }
    }
*/


    
    
    /**
     * Getting Navision-Credentials for Webservices from the backend-settings. 
     * @See \app\code\Pixelmechanics\ExportOrder\etc\adminhtml\system.xml
     */            
    private function getNavisionCredentials() {
        $this->navision_url = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navision/webservice_url')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->navision_login = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navision/webservice_login')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->navision_pwd = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navision/webservice_pw')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->navision_soapAction = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navision/webservice_soapaction')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->navision_url=="") {
            $this->addLogMessage("FEHLER: Bitte zuerst korrekte Zugangsdaten für Navision eintragen in Shops -> Config -> Pixelmechanics -> Navision.", "error"); 
            return false;
        }
        if ($this->navision_login=="") {
            $this->addLogMessage("FEHLER: Bitte zuerst korrekte Zugangsdaten für Navision eintragen in Shops -> Config -> Pixelmechanics -> Navision.", "error"); 
            return false;
        }
        if ($this->navision_pwd=="") {
            $this->addLogMessage("FEHLER: Bitte zuerst korrekte Zugangsdaten für Navision eintragen in Shops -> Config -> Pixelmechanics -> Navision.", "error"); 
            return false;
        }
        if ($this->navision_soapAction=="") {
            $this->navision_soapAction = "default";
        }
        // preprint(array($this->navision_url, $this->navision_login, $this->navision_pwd, $this->navision_soapAction), __FILE__.__LINE__); die();
        return true;
    }
    




    /**
     * @deprecated
     */
    public function getFromCSV() {
       // get directory path from pm_mage_helper.php
        $dir = importProductdirectoryPath(); 
       // get filename with path from pm_mage_helper.php
        $csvFileName = importProductCSVFileName();
        $file = $this->_dir->getPath($dir);
        $fileName = $file . '/'.$csvFileName;
        if (!file_exists($fileName)) {
           $this->_logger->info("Import CSV file not found");
            die("Import CSV file not found");
        }

        // Open the csv file in read mode to update product qty and stock status
        if (($handle = fopen($fileName, "r")) !== FALSE) {

            $count = 0;
            // For saving header of CSV file
            $csvHeader = array();
            
            // Loop through each row
            while (($row = fgetcsv($handle, 0, ',')) !== FALSE) 
            {   
                // Get header from first row
                if ($count==0) { 
                    foreach ($row as $key => $col_name) {
                         $csvHeader[] = $row[$key];
                    }
                    $count++; 
                    continue; 
                }

                /**
                 * Prepare csv data
                 **/
                // Array which will contain the product data of the current row
                $csvdata = array();

                // Fill the csvdata array with its data based on the header which is defined above
                foreach ($csvHeader as $key => $col_name) {
                    $csvdata[$col_name] = $row[$key];
                }
                
                // Get product object on the basis of product SKU
                
                $_product = $this->_objectManager->create('\Magento\Catalog\Model\Product')->loadByAttribute('sku',  $csvdata['sku']);
            
               try {
                   // check if product exist then update product qty and stock status
                    if ($_product) {
                      
                    // check stock status should be either 0 or 1    
                        if($csvdata['is_in_stock']==0 || $csvdata['is_in_stock'] ==1){
                            
                    // check qty should be integer    
                            if (filter_var($csvdata['qty'], FILTER_VALIDATE_INT)) {
                                
                                $_product->setStockData(
                                array(
                                  'is_in_stock' => (int)$csvdata['is_in_stock'], // Stock Availability of product
                                  'qty' => (int)$csvdata['qty']
                                  )
                                );
                                
                                // rest attributes can be updated by attribute code like "$product->setName('updated name');"
                            
                            // save product
                                $_product->save();

                                echo 'product with SKU'. $csvdata['sku'].' is updated with qty '.$csvdata['qty'] .' and stock status '.$csvdata['is_in_stock'] ."\n" ;
                                $this->_logger->info('product with SKU'. $csvdata['sku'].' is updated with qty '.$csvdata['qty'] .' and stock status '.$csvdata['is_in_stock']);
                            }
                            else{
                                echo  'product with SKU '. $csvdata['sku'].' is unable to update as qty is not integer' ."\n" ;
                                $this->_logger->info('product with SKU '. $csvdata['sku'].'is unable to update as qty is not integer');
                            }
                        }
                        else{
                            echo 'product with SKU '. $csvdata['sku'].' is unable to update as is_in_stock value should be 0 or 1 but in CSV value is '.$csvdata['is_in_stock']."\n";
                            $this->_logger->info('product with SKU '. $csvdata['sku'].' is unable to update as is_in_stock value should be 0 or 1 but in CSV value is '.$csvdata['is_in_stock']);
                        }

                    } else {
                        echo 'product with SKU'. $csvdata['sku'].' not found'."\n";
                        $this->_logger->info('product with SKU'. $csvdata['sku'].' not found');
                    }
                }
                catch (\Exception $e) {
                    echo "Cannot retrieve products from Magento: ".$e->getMessage()."<br>";
                    return;
                }
            }
        }
        
        die();
    }

}
