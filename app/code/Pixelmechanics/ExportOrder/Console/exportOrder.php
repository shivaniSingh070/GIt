<?php
/**
 * This Script is executed by the shell-command: "php bin/magento navision:exportOrder"
 * It loads all orders that are in the state "processing" and have no ERP-ID set.
 * Then the ExportOrder-Model will be called to export these orders again.
 * 
 * @link https://trello.com/c/68IXDl4E/61-order-export-bei-paypal-manchmal-ohne-items
 */

namespace Pixelmechanics\ExportOrder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class exportOrder extends Command
{
    
    /** @var \Pixelmechanics\ExportOrder\Model\Order\Export **/
    public $exportModel;
    
    /** @var \Magento\Framework\App\State **/
    private $state;
    
    // Stores all messages, so they can be sent in the end by email.
    private $log;
    
    
    protected function configure() {
        $this->setName('navision:exportOrder');
        
        // Achtung: Manche Bestellungen sind auf complete, bevor sie exportiert wurden: https://trello.com/c/EpC6FpW9/61-2020-03-webshopstatus-complete-ohne-export-nach-navision-paypal-express#comment-5e79d1c74541dc4004f938f7
        $this->setDescription('Exporting all orders with Status processing, that do not have an ERP-ID set already to NaVision');

        parent::configure();
    }



    protected function execute(InputInterface $input, OutputInterface $output) {
        
        
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->engelsHelper = $this->objectManager->create('Pixelmechanics\Engelsrufer\Helper\Data');
        
        // @see: app/code/Pixelmechanics/Engelsrufer/Helper/Data.php
        $this->navision_url = trim($this->engelsHelper->getStoreConfig('pixelmechanics_configuration/navision/webservice_url')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        

        // for the log-output, we define a unique string for each run. Currently it is just a datetime-stamp.
        $this->runID = date("Y-m-d_His");
        
        $this->directory = $this->objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        include_once($this->directory->getRoot()."/pm_navision_helper.php");
        include_once($this->directory->getRoot()."/defines.php");
    
        $this->productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductRepository');
        
        // load the model @todo: Perhaps there is a better way? 
        $modelLoaded = $this->getExportModel();
        if (!$modelLoaded) {
            return false;
        }    
        
        // load the orders..
            $this->getOrders();
        
        // loop through the loaded orders of $this->orders and call the Model-function: 
            $this->exportOrders();

        // Send all Logs to an email receiver
            $this->sendLogEmail($this->log);
    }



    /**
     * Get all Orders from Magento that are in status Processing and the field ERP-ID is empty.
     * PM RH, 11.12.2019
     * @link https://trello.com/c/68IXDl4E/61-order-export-bei-paypal-manchmal-ohne-items
     **/
        public function getOrders() {
            
            $orderCollection = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection();

            // Load all orders that are in status="Processing" and the field "erp_id" is EMPTY
            // add only the Order-IDs to the array.
                $this->orders = array();
                foreach($orderCollection as $orderRow){
                    $erp_id = trim($orderRow->getData('erp_id')); 
                    $order_no = $orderRow->getData('increment_id'); 
                    $order_id = $orderRow->getEntityId(); 
                    $state = $orderRow->getState();
                    
                    if($state == 'processing' && empty($erp_id)){
                        $this->orders[$order_id] = $order_no;
                    }
                    /* Orders paid with a gift card go straight to status "complete" if the oder total is 0
                    *  We still want to export those orders
                    * PM LB 04/2022 https://trello.com/c/fGjI4iPU/280-probleme-beim-export
                    */
                    else if ($state = "complete" && $orderRow->getData("grand_total") == 0.0000 && empty($erp_id)) {
                        $this->orders[$order_id] = $order_no;
                    } else {
                        // preprint("#$order_id ($erp_id) = $state", "Order not added, wrong state., ".__FILE__.__LINE__);
                    }
                }
            
            // preprint($this->orders, "Loaded Orders, ".__FILE__.__LINE__); die();
            $msg = count($this->orders)." orders loaded that are in processing and do not have an ERP-ID";
            $this->addToLog($msg, "success");
            return !empty($this->orders);            
        }



    /**
     * invoke the order-export-model to export the stuff.
     * PM RH and NA
     * https://trello.com/c/68IXDl4E/61-order-export-bei-paypal-manchmal-ohne-items
     **/
        public function exportOrders() {

            
            if (!isset($this->orders) || empty($this->orders)) {
               $this->addToLog("No Orders loaded.", __FILE__.__LINE__);
               return false;
            }
            
            
            
           /**
            * Loop through the order-id-array and call the models "export"-function.
            * @todo: continue here.
            */
                $this->addToLog("Exporting all orders via API to: {$this->navision_url}, beginning at ".date("d.m.Y H:i:s"), __FILE__.__LINE__);
                $start = microtime(true);
                
                    foreach ($this->orders as $order_id => $order_no) {
                        $this->addToLog("Exporting Order #{$order_no} (ID: {$order_id})", "See ".__FILE__.__LINE__);
						ob_start();
							$this->exportModel->exportOrder($order_id, false, array("processing", "complete"));
							$msg .= ob_get_contents();
							$this->addToLog($msg, "Export-Status-Meldung");
						ob_flush();
                    }
                    
                $end = microtime(true);
                $durationLoop = $end-$start;
                
                
                $count = count($this->orders);
                $this->addToLog("Exported $count Orders in $durationLoop seconds on ".date("d.m.Y H:i:s"), __FILE__.__LINE__);

                
            /*
            $msg = "$success erfolgreich importiert, $failed Fehler.";
            $msg .= "\nDauer API-Abfragen: $durationAPILoad seconds, Dauer Import Magento: $durationImportMage";
            $msg .= "\n\nLog-Datei auf Server: ".$this->logFilename;
            $this->addToLog($msg, __FILE__.__LINE__);
            
            $msg .= "\n\nFehler: \n".print_r($errors, 1);
            $msg .= "\n\nErfolgreich: \n".print_r($successes, 1);
             */
        }
        
        
    
    /**
     * Get the Model, so we can call the methods from it.
     */
        private function getExportModel() {
            
            /**
             * Set the State, otherwise the model cannot be loaded
             */
                // $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); // or \Magento\Framework\App\Area::AREA_ADMINHTML, depending on your needs
                $this->state = $this->objectManager->get('Magento\Framework\App\State');
                $this->state->setAreaCode('frontend'); // evtl auch "admin" oder base ?

            $this->exportModel = $this->objectManager->get('\Pixelmechanics\ExportOrder\Model\Order\Export');
            if (get_class($this->exportModel)!=="Pixelmechanics\ExportOrder\Model\Order\Export") {
                return false;
            }
            
            return true;
        }
    
    
    
    /**
     * Store debugging text into a text-file in var/log related to the ID of an order.
     * @param type $order_id
     * @param string $msg
     */
        public function addToLog($msg, $where="", $type="") {
            
            $this->logFilename = MAGE_ROOT."/var/log/nav_orderexport_".$this->runID.".txt";
            
            preprint($msg, $where);
            $this->log .= $msg."\n";
			
			
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
					
				// $this->import_emailadress = "rh@pixelmechanics.de";
                    
                $subject = "Engelsrufer.de OrderExport Navision -> Magento ({$this->runID})";
                print "\n\n----\nSende Email an: {$this->import_emailadress}\n";
                
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
    
    
    
}
