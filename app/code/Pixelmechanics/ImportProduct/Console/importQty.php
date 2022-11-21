<?php
//siehe https://trello.com/c/ds9h7Vo1/34-za-api-anbindung-navision-navision-entwicklung-teil-2a-import-produkt-qty

namespace Pixelmechanics\ImportProduct\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class importQty extends Command
{
    /* this would be the best way. But construct does not seem to work here.
    So we use the object-manager below.
    @link https://www.quora.com/How-do-I-load-a-product-by-SKU-in-Magento-2
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        parent::__construct();
    }
    */

    protected function configure()
    {
        $this->setName('navision:importQty');
        $this->setDescription('Import the B2C Product quantities from NAVision to Magento');

        parent::configure();
    }
    
    
    
    /**
     * Store debugging text into a text-file in var/log related to the ID of an order.
     * @param type $order_id
     * @param string $msg
     */
        public function addToLog($msg, $where="", $type="") {
            
            $this->logFilename = MAGE_ROOT."/var/log/nav_import_".$this->runID.".txt";
            
            preprint($msg, $where);
            
            if (!is_string($msg)) {
                $msg = print_r($msg, 1);
            }
            
            if (!empty(trim($where))) {
                $msg = $msg."  ($where)";
            }
            
            if (!empty(trim($type))) {
                $msg = $type." :: ".$msg;
            }
            
            $msg = $msg."\n-----------\n\n";
            file_put_contents($this->logFilename, $msg, FILE_APPEND);
        }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->engelsHelper = $this->objectManager->create('Pixelmechanics\Engelsrufer\Helper\Data');

        // for the log-output, we define a unique string for each import-run. Currently it is just a datetime-stamp.
        $this->runID = date("Y-m-d_His");
        
        $this->directory = $this->objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        include_once($this->directory->getRoot()."/pm_navision_helper.php");
        include_once($this->directory->getRoot()."/defines.php");
    
        $this->productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $this->ProductRepositoryInterface = $this->objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface'); // @link https://www.quora.com/How-do-I-load-a-product-by-SKU-in-Magento-2

        $this->getFromNAVISION();
    }



    /**
     * Call the SOAP-API and retrieve the inventory-data for products from the B2C-Stock.
     * PM LB, 06.11.2019
     * @link https://trello.com/c/ds9h7Vo1/34-za-api-anbindung-navision-navision-entwicklung-teil-2a-import-produkt-qty
     * @link https://trello.com/c/pk8egBYL/9-api-09import-product-qty-from-navision-to-magento
     **/
        public function getFromNAVISION() {


            $result = $this->getNavisionCredentials();
            if ($result===false) {
                return false;
            }
           if (!$this->importFromNavision) {
               $this->addToLog("Import von Navision wurde deaktiviert. Siehe Backend -> Shops -> Konfiguration -> Pixmex/Navision -> Import From Navision", __FILE__.__LINE__);
               return false;
           }

           /**
            * Load all the product-informations from the API
            * The method will return SKU and QTY
            */
                $this->addToLog("Loading {$this->importSize} products per Page via API from: {$this->navision_url}, beginning at ".date("d.m.Y H:i:s"), __FILE__.__LINE__);
                $start = microtime(true);
                
                    $filter = array("Location_Filter" => "_B2C"); // nur Bestände aus diesem Lager laden. Für "ERS-LOVE-M" sollte dies eine 0 sein.
                    if (!empty($this->importArticleGroupCodes)) {
                        // $filter["Item_Category_Code"] = $this->importArticleGroupCodes;
                    }
                        
                    // @todo: DEV das hier wieder auskommentieren
                        // $filter["Item_No"] = "ERS-LOVE-M"; // Nur Artikel laden, deren Preis >0 ist.
                        // $filter["Unit_Cost"] = ">0"; // Nur Artikel laden, deren Preis >0 ist.
                        // $filter["Is_Webshop_Item"] = "false"; // wird vermutlich nur für B2B Shop verwendet.
                        // $filter["No"] = "ERS-LOVE-M";
                        // $filter["Item_No"] = "ERS-LOVE-M";
                        $this->addToLog($filter, "Filter, siehe ".__FILE__.__LINE__);
                    
                    
                /**/
                $productQtys = $this->navHelper->getAllProductQuantities($filter, $this->importSize);
                // preprint($productQtys, __FILE__.__LINE__, true);
                /* @todo: ENTFERNEN for deployment! /* * /
                    $productQtys = array(
                        
                        // Kann man kaufen: http://engelsrufer.rh/ers-love-engelsrufer-klangkugel-love
                        array(
                            "sku" => "ERN-05-EDEN-XS-ZIB",
                            "qty" => 1,
                            "Blocked_for_Purchase" => "true",
                            "Description" => "#wegen Hashtag: disablen",
                        ),

                        // Kann man kaufen: http://engelsrufer.rh/ers-love-engelsrufer-klangkugel-love
                        array(
                            "sku" => "ERS-LOVE-S",
                            "qty" => 1,
                            "Blocked_for_Purchase" => "false",
                            "Description" => "wegen keinem Hashtag: enablen",
                        ),
                        
                        // Später verfügbar: http://engelsrufer.rh/ers-love-engelsrufer-klangkugel-love
                        array(
                            "sku" => "ERS-LOVE-L",
                            "qty" => 1,
                            "Blocked_for_Purchase" => "false",
                            "Description" => "wegen keinem Hashtag: enablen",
                        ),
                        
                        // disabled, darf nirgends mehr erscheinen: http://engelsrufer.rh/ers-love-engelsrufer-klangkugel-love
                        array(
                            "sku" => "ERS-LOVE-M",
                            "qty" => 1,
                            "Blocked_for_Purchase" => "true",
                            "Description" => "#wegen Hashtag: disablen",
                        ),
                    );
                    /**/    
                    
                    
                $end = microtime(true);
                $durationAPILoad = $end-$start;
                
                if ($productQtys===false || empty($productQtys)) {
                    $this->addToLog($this->navHelper->error, __FILE__.__LINE__); 
                    // $this->addToLog($productQtys, __FILE__.__LINE__);
                    die();
                    return false;
                }
                $prodCount = count($productQtys);
                $this->addToLog("Api returned $prodCount products in $durationAPILoad seconds on ".date("d.m.Y H:i:s"), __FILE__.__LINE__);

            


            $start = microtime(true);
            $this->productObject = $this->objectManager->get('Magento\Catalog\Model\ProductFactory');
            $this->state = $this->objectManager->get('Magento\Framework\App\State');
            $this->state->setAreaCode('frontend'); // evtl auch "admin" oder base ?
            $statusEnabled = 1; // \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            $statusDisabled = 2; // \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED

            $storeID = 0;
            $success = 0;
            $failed  = 0;
            $errors = array();
            $successes = array();
            foreach ($productQtys as $prod) {
                $sku = trim($prod["sku"]);
                $qty = $prod["qty"];
                $is_in_stock = 1;
                if ($qty<=0) {
                    $is_in_stock = 0;
                }
                
                /**
                 * PM RH 17.02.20: https://trello.com/c/zqdB5UY5/
                 */
                    $Blocked_for_Purchase = false; // "true" oder "false"
                    if (isset($prod["Blocked_for_Purchase"]) && $prod["Blocked_for_Purchase"]=="true") {
                        $Blocked_for_Purchase = true; 
                    }
                



                // 1. Laden
                    try {
                        // can only load enabled products. Disabled products are not part of this collection.
                        // $product = $this->productObject->create()->setStoreId($storeID)->loadByAttribute('sku', $sku);
                        $product = $this->ProductRepositoryInterface->get($sku);
                        if (!$product) {
                            $errors[] = "ERROR: $sku konnte nicht geladen werden";
                            $failed++;
                            continue;
                        }
                        // preprint($product->getName(), __FILE__.__LINE__, true); die();
                        // else $this->addToLog($product->getName()." geladen..", __FILE__.__LINE__);
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "ERROR: Produkt '$sku' nicht gefunden oder Fehler beim Laden: ".$e->getMessage();
                        // print $msg."\n";
                        continue;
                    }
                    

                // 2. Stockdata setzen
                    try {
                        $stockData = array(
                            'is_in_stock' => $is_in_stock, // Stock Availability of product
                            'qty' => $qty
                        );
                        $product->setStockData($stockData);
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "WARNING: Produkt '$sku' Stock Data nicht setzbar: ".$e->getMessage();
                        // print $msg."\n";
                        continue;
                    }


                // 3. Verfügbarkeit setzen: Wenn Menge <= 0 und "für einkauf gesperrt", dann disablen.
                    try {
                        

                        /**
                         * When Qty>0, always enable the product, if it was disabled before (2=disabled)
                         * @link https://magecomp.com/blog/update-product-attribute-value-product-quickly-magento-2/
                         * 
                         * PM RH 20.02.2020: NICHT automatisch enablen,
                         *          siehe https://trello.com/c/hc1vLXci/#action-5e4e6c8cd2bef977e2ca853e, was alle Kriterien erfüllt um aktiviert zu werden, aber es noch nicht darf.
                         *
                         * /
                            if ($qty>0 && $product->getStatus()==2) {
                                $successes[] = $product->getName()." (SKU: $sku) set to ENABLED (from {$product->getStatus()} to $statusEnabled) because: Qty = $qty ";
                                $product->setStatus($statusEnabled);
                            }
                            /**/
                        
                            $product->setData("available_later", 0);
                        


                        /**
                         * Zuerst prüfen, ob die importierte Description mit einem "#" beginnt?
                         * Weil Echtgold ist für Einkauf gesperrt, aber in Beschreibung ist "#" nicht am Anfang.
                         * Deshalb sollten diese doch gekauft werden können
                         * siehe https://trello.com/c/hc1vLXci/48-produkte-disablen-wenn-nie-mehr-verf%C3%BCgbar-beim-import#comment-5e4b97281a8d3a6bbfcc5a5b
                         */
                            if ( $Blocked_for_Purchase===true && (strpos($prod["Description"], "#")!==0)) {
                                $successes[] = $product->getName()." (SKU: $sku) is blocked for Purchase, but Description does not begin with #. So it will be available (example: Echtgold)";
                            }

                        /**
                         * Disable Product, when "blocked for purchase" and no item available anymore
                         * For "Echtgold" Articles, the same conditions apply, but the description does not begin with "#".
                         * Only articles, which descriptions begin with "#" can be disabled.
                         */
                            if ($qty<=0 && $Blocked_for_Purchase===true && (strpos($prod["Description"], "#")===0)) {
                                $successes[] = $product->getName()." (SKU: $sku) set to DISABLED ($statusDisabled) because Blocked_for_Purchase ";
                                $product->setData("available_later", 0);
                                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                            }                            

                        /**
                         * Available later => true, if not blocked for purchase
                         */
                            if ($qty<=0 && $Blocked_for_Purchase===false) {
                                $successes[] = $product->getName()." (SKU: $sku) set to AVAILABLE LATER";
                                $product->setData("available_later", 1);
                                // $product->getResource()->saveAttribute($product, 'available_later');
                            }
                            
                            
                        $product->setStockData($stockData);
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "WARNING: Produkt '$sku' Stock Data nicht setzbar: ".$e->getMessage();
                        // print $msg."\n";
                        continue;
                    }    
                    

                // 99. Speichern
                    try {
                        // save product
                        $product->save();
                        $success++;
                        $successes[] = $product->getName()." (SKU: $sku) is updated with Qty $qty";
                        // $this->addToLog("SUCCESS: ".$msg, __FILE__.__LINE__);
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "ERROR: Produkt '$sku' nicht speicherbar: ".$e->getMessage();
                        // print $msg."\n";
                        continue;
                    }



                    print "\r{$success} Importiert, {$failed} Probleme (Auflistung folgt am Ende des Scripte)";
            } // foreach productQtys
            $end = microtime(true);
            $durationImportMage = $end-$start;
            
            if (!empty($errors)) {
                $this->addToLog($errors, "Fehler");
            }
            
            if (!empty($successes)) {
                $this->addToLog($successes, "Erfolgreich");
            }
            
            $msg = "$success erfolgreich importiert, $failed Fehler.";
            $msg .= "\nDauer API-Abfragen: $durationAPILoad seconds, Dauer Import Magento: $durationImportMage";
            $msg .= "\n\nLog-Datei auf Server: ".$this->logFilename;
            $this->addToLog($msg, __FILE__.__LINE__);
            
            $msg .= "\n\nFehler: \n".print_r($errors, 1);
            $msg .= "\n\nErfolgreich: \n".print_r($successes, 1);
            $this->sendLogEmail($msg);
        }
    
        
        
                
        /**
         * Send the Message at the end of the import by email
         * PM RH 03.12.2019
         * @link https://trello.com/c/ds9h7Vo1/34-za-api-anbindung-navision-navision-entwicklung-teil-2a-import-produkt-qty#action-5de6708087f01c25aaf4bffa
         */
            public function sendLogEmail($msg) {
                
                $msg = trim($msg);
                if (empty($msg)) {
                    return false;
                }
                
                
                /**
                 * To this E-Mailadress, the Logoutput will be sent after importing.
                 * If the field is empty, no email will be sent.
                 * Also comma-separated values are possible, that's why we strip all spaces here.
                 */
                    $this->import_emailadress = str_replace(" ", "", trim($this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navisionImport/import_emailadress')));
                    if (empty($this->import_emailadress)) {
                        return false;
                    }
                    
                    
                $subject = "Engelsrufer.de Bestandsimport Navision -> Magento ({$this->runID})";
                print "Sende Email an: {$this->import_emailadress}\n";
                
                // @todo: evtl mit Magento senden?
                // Siehe https://magento.stackexchange.com/questions/87237/magento-2-sending-email-programmatically oder https://www.metagento.com/blog/magento-2-send-email-programmatically
                
                
                $replyTo = "robertheine+fwz3wwst6ghio7kodtbc@boards.trello.com"; // bei Antwort an diese Adresse, wird eine Karte im Navision-Board erstellt. https://trello.com/b/bD1va1Gz/u-engelsrufer-m2-schnittstelle-navision
                $nameFrom = "Magento2-Navision Importer";
                // SMTP Daten siehe https://trello.com/c/kBRkDBW0/29-e-mail-versand-smtp-tls#comment-5d4179d3cee9828836063b13
                $from = "order@engelsrufer.de"; // PW: ER6QC!cZ


                $sent = mail($this->import_emailadress, $subject, $msg);
                if (!$sent) {
                    /*
                    $email = new \Zend_Mail();
                    $email->setSubject($subject);
                    $email->setBodyText($msg);
                    $email->setFrom($from, $nameFrom);
                    $email->addReplyTo($replyTo);
                    foreach (explode(",", $this->import_emailadress) as $to) {
                        $email->addTo($to);
                    }
                    $sent = $email->send();
                    
                    /**
                     * @todo: Falls auch das nicht klappt, Magento Transport-Builder https://magento.stackexchange.com/a/284983
                     */
                }
                if (!$sent) {
                    print "Email konnte nicht gesendet werden\n";
                }

                return true;
            }
    
    
    
    
    /**
     * Getting Navision-Credentials for Webservices from the backend-settings. 
     * @See \app\code\Pixelmechanics\ExportOrder\etc\adminhtml\system.xml
     */
        private function getNavisionCredentials() {

            /**
             * Is being checked in the other function.
             */
                $this->importFromNavision = (int)$this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navisionImport/doImportFromNavision'); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            /**
             * The URL where to import from.
             * The Import can load the qty frmo the live-navision, while the export writes to the DEV-Navision.
             */
                $this->navision_url = $this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navision/webservice_url'); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $this->navision_url_import = trim($this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navisionImport/webservice_url_import')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if (!empty($this->navision_url_import)) {
                    $this->navision_url = $this->navision_url_import;   
                }
                
                
            /**
             * In production/cli Mode, we want to make as few calls as possible
             * without returning too many Results (may cause a timeout)
             */
                $this->importSize = (int)$this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navisionImport/import_size'); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if (ENVIRONMENT!=="production") {
                    // $this->importSize = 50; // Smaller set Size for Debugging
                    // $this->addToLog($this->importSize, __LINE__); die();
                }
                
                
            /**
             * Speed this up by only importing Articles with these Group-Codes.
             * Example: "ER,ERWATCH,GOLDHE,GOLDER,HE,KT"
             * @link: https://trello.com/c/fodPvSak/12-funktion-lagerplatz-magento-artikelmengenimport-vom-b2c-lager
             */
                $this->importArticleGroupCodes = trim($this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navisionExport/debitorartikelgruppencodes'));
                if (!empty($this->importArticleGroupCodes)) {
                    $this->importArticleGroupCodes = explode(",", $this->importArticleGroupCodes);
                }
                

            $this->navision_login = $this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navision/webservice_login'); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->navision_pwd = $this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navision/webservice_pw'); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->navision_soapAction = $this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navision/webservice_soapaction'); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

            $this->navHelper = new \Nav($this->navision_url, "Artikel", $this->navision_login, $this->navision_pwd, $this->navision_soapAction);


            // $this->addToLog(array($this->navision_url, $this->navision_login, $this->navision_pwd, $this->navision_soapAction), __FILE__.__LINE__); die();
            return true;
        }
}
