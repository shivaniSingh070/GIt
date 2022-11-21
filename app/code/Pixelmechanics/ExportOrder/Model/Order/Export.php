<?php

/**
 * @author : AA
 * @template-Version : Magento 2.3.1
 * @description : ExportOrder Export Model to generate the XML and the PDF file
 * @date : 19.06.2019
 * @Trello: https://trello.com/c/7yfEDXmg
 */

namespace Pixelmechanics\ExportOrder\Model\Order;

class Export extends \Magento\Framework\Model\AbstractModel {

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Pixelmechanics\ExportOrder\Helper\Data
     */
    protected $_exporthelper;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $_orderInterface;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var \Pixelmechanics\ExportOrder\Logger\Logger
     */
    protected $_logger;

    /**
     * @var False
     */
    protected $used_coupon;

    /**
     * @var False
     */
    protected $different_shipping;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Pixelmechanics\ExportOrder\Helper\Data $_exporthelper, 
        \Magento\Sales\Api\Data\OrderInterface $orderInterface, 
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface, 
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, 
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Pixelmechanics\ExportOrder\Logger\Logger $logger,
        \Psr\Log\LoggerInterface $psrlogger, // Siehe https://magento.stackexchange.com/questions/119992/exception-handling-in-magento-2
        \Magento\Framework\Message\ManagerInterface $messageManager, // PM RH 17.10.2019: https://www.rakeshjesadiya.com/display-success-and-error-messages-using-magento-2/
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transXInterface, // PM RH 18.10.2019 to retrieve the Transaction ID
        \Magento\Catalog\Helper\Data $taxHelper // Get the itemprice WITH Tax seems to be more complicated. See https://gielberkers.com/get-product-price-including-excluding-tax-magento-2/
    ) {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_exporthelper = $_exporthelper;
        $this->_orderInterface = $orderInterface;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_logger = $logger;
        $this->psrlogger = $psrlogger; // Siehe https://magento.stackexchange.com/questions/119992/exception-handling-in-magento-2
        $this->used_coupon = false;
        $this->different_shipping = false;
        $this->messageManager = $messageManager; // PM RH 17.10.2019: https://www.rakeshjesadiya.com/display-success-and-error-messages-using-magento-2/
        $this->transXInterface = $transXInterface; // PM RH 18.10.2019 to retrieve the Transaction ID
        $this->taxHelper = $taxHelper; // PM RH 22.10.2019, @link https://magento.stackexchange.com/a/238706
        
        /**
         * To not show certain Messages in the frontend. 
         * PM RH 24.10.2019
         */
            $state =  $this->_objectManager->get('Magento\Framework\App\State');
            if($state->getAreaCode() == 'frontend') {
                $this->is_frontend = true;
                $this->is_backend = false;
            }
            //backend
            else {
                $this->is_frontend = false;
                $this->is_backend = true;
            }
            
            

        include_once(MAGE_ROOT."/pm_navision_helper.php");
        $result = $this->getNavisionCredentials();
        if ($result===false) {
            return false;
        }		
       
    }
    
    
    public function test() {
        return "This is the ExportOrder Model. See ".__FILE__.__LINE__;
    }
    
    
    
    /**
     * @link https://magento.stackexchange.com/questions/169494/magento-2-load-order-by-id-in-customer-account-order-view
     * @param type $id
     * @return Magento\Sales\Model\Order\Interceptor
     */
        public function getOrderByID($order_id) {

            // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // $this->order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderRep = $objectManager->create(\Magento\Sales\Model\OrderRepository::class);
            	
            //$this->order = $this->_orderInterface->load($order_id);
            $this->order = $orderRep->get($order_id);
            // preprint($this->order->getExtensionAttributes(), __FILE__.__LINE__, true); die();
                       
            $msg = __FILE__.__LINE__.": ".get_class($this->order)."() -> ";//.print_r($this->order->toArray(), 1);
            $this->orderLog($order_id, $msg);
            
            $items = $this->order->getAllVisibleItems();
            
            if (empty($items)) {
                $message = "WARNING: Order-Object ".get_class($this->order)." does not contain Items!";
                if (ENVIRONMENT != "production") {
                    $message .= "\n Perhaps the Order was not fully loaded? See ".__FILE__.__LINE__;
                }
                $this->addLogMessage($message, "debug");
                $this->orderLog($order_id, $message);
                
                // preprint($message, __FILE__.__LINE__);
                // preprint($this->order->toArray(), __FILE__.__LINE__);
                // preprint(get_class_methods($this->order), get_class($this->order).", ".__FILE__.__LINE__); die();
            } else {
                $message = "Order-Object ".get_class($this->order)." HAS  Items :-) ".__FILE__.__LINE__;
                $this->orderLog($order_id, $message);
            }

        }
        
    
    /**
     * Store debugging text into a text-file in var/log related to the ID of an order.
     * @param type $order_id
     * @param string $msg
     */
        public function orderLog($order_id, $msg) {
            $orderLogFilename = MAGE_ROOT."/var/log/orderexport_".$order_id.".txt";
            $msg = date("d.m.Y H:i:s")." :: ".$msg."\n-----------\n\n";
            file_put_contents($orderLogFilename, $msg, FILE_APPEND);
        }
    
        
    /**
     * reads the Sales-Order ID/Key from a text-file as temporary solution
     * @param type $order_id
     * @param string $msg
     */
        public function getNavisionDataForOrderID($order_id) {

            
            if (!isset($this->order)) {
                $this->getOrderByID($order_id);
            }
            
            $return = array();
            
            
            
            // check, if we can get the infos from a file (old way until 18.11.2019)
                $orderLogFilename = MAGE_ROOT."/orderexport/order_".$order_id.".dat";
                if (file_exists($orderLogFilename)) {
                    
                    // aus der Datei
                    $return = unserialize(file_get_contents($orderLogFilename));
                    
                    // ist noch nach der alten Logik gespeichert, also nach der neuen abspeichern.
                        if (!isset($return["salesorder_no"]) && isset($return["No"])) {
                            $this->saveNavisionDataForOrderID($order_id, array("salesorder_no" => $return["No"], "debitor_no" => $return["Sell_to_Customer_No"])); // Now store the data into the order, so we can get rid of the files.
                        }
                    // und die Datei dann löschen.
                        @unlink($orderLogFilename);
                }
            
            
            /**
             * Load the data from the order, see https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing#comment-5dd26e6fc76d4f208972daa6
             */
                if (!isset($return["No"])) {
                    $return = array(
                        "No" => $this->order->getErpId(),
                        "debitor_no" => $this->order->getDebitorNo()
                        );
                    // preprint($return, __FILE__.__LINE__); die();
                }
                
                
            return $return;
        }
        
        
    /**
     * Stores the Sales-Order ID/Key as a temporary value into a text-file.
     * @param type $order_id
     * @param string $msg
     * @link: MageSaveOrder https://magento.stackexchange.com/questions/163916/magento-2-how-to-add-custom-data-in-order-email
        https://magento.stackexchange.com/questions/180371/magento-2-save-additional-data-to-order
        https://www.yereone.com/blog/magento-2-how-to-add-new-order-attribute/
        https://community.magento.com/t5/Magento-2-x-Programming/How-to-add-custom-field-in-order-sales-table-and-used-it/td-p/103625
     */
        public function saveNavisionDataForOrderID($order_id, $data) {
            
            /**
             * See https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing#comment-5dd26e6fc76d4f208972daa6
             */
                if (!isset($this->order)) {
                    $this->getOrderByID($order_id);
                }
                
            /**
             * Saving the custom-attributes: 
             * @link https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing#comment-5dd2d0bf5e05358763fe0e27
             * @link https://bitbucket.org/pixelmechanics/engelsrufer-relaunch/pull-requests/576/feature-20191118-orderattribute-aa/diff
             */
                if (isset($data["salesorder_no"])) {
                    $this->addLogMessage("Storing Nav-Salesorder-No {$data["salesorder_no"]} to Magento Order $order_id", "debug");
                    $this->order
                        ->setData("erp_id", $data["salesorder_no"])
                        ->save();
                }
                
                if (isset($data["debitor_no"])) {
                    $this->addLogMessage("Storing Nav-Debitor-No {$data["debitor_no"]} to Magento Order $order_id", "debug");
                    $this->order
                        ->setData("debitor_no", $data["debitor_no"])
                        ->save();
                }
                
                
            // No storing in a file is needed anymore. So we can return now.
                return true; 
                
                
            
            /**
             * Fallback / Backup-Method: Store the infos in a file.
             */
            
                $folder = MAGE_ROOT."/orderexport/";
                $orderLogFilename = $folder."order_".$order_id.".dat";
            
                // Add the Directory and HTACCESS if they dont exists
                    @mkdir($folder, 0755);
                    if (!file_exists($folder.".htaccess")) {
                        file_put_contents($folder.".htaccess", "deny from all");
                    }

                // Put the contents into the file.
                    $result = file_put_contents($orderLogFilename, serialize($data));
                    if ($result) {
                        $this->addLogMessage("Storing serialized Navision-Data to file '{$orderLogFilename}'", "debug");
                    } else {
                        $this->addLogMessage("Could not save the file '{$orderLogFilename}'. No problem because of custom-orderattributes ;-)", "debug");
                    }
                    
            return $orderLogFilename;
        }
        
        

    /**
     * Main function to export orders.
     *
     * @param string $order_id (Magento order entity ID)
     * @param string $status Order status like "processing" or "complete" 2022_04 PM LB https://trello.com/c/fGjI4iPU/280-probleme-beim-export
     * */
        public function exportOrder($order_id, $order=false, $status=false) {

            


            $this->order_id = $order_id;
            $this->orderData = null;
                
                
            // Check if an order with this id was found and loaded sucessfully.
                $this->getOrderByID($order_id);
                if (!method_exists($this->order, "getId")) {
                    $this->_logger->error('Order. # '. $order_id. 'exists, but could not be loaded from Magento.');
                    return;
                }

            // Get the order's increment id
                $this->order_increment_id = $this->order->getIncrementId();
                // preprint($this->order_increment_id, __FILE___.__LINE__); die();
                
            // Convert the Order-Datails from Magento to exportable values.
                $this->generateOrderdataArray($order_id, $this->order_increment_id);
                 //preprint($this->orderData, __FILE__.__LINE__, true); die();


                /* Do not export orders if the order state does not match (for cron export we only want orders with status "processing")
                *  @link https://trello.com/c/fGjI4iPU/280-probleme-beim-export
                * PM LB 2022_04
                */
                if($status != false && !in_array($this->orderData["State"], $status)) {
                    $this->_logger->error('Order. # '. $order_id. 'status is not '.$status. ', will not export');
                    return;
                }

                
            /**
             * Generate the XML file in the path of exported orders.
             * Can be switched on/off by a backend-setting: /pmadmin/admin/system_config/edit/section/pixelmechanics_configuration/
             * @See \app\code\Pixelmechanics\ExportOrder\etc\adminhtml\system.xml
             */
                $doCreateXML = (int)($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/createXML')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($doCreateXML) {
                    $result = $this->createXmlFile($this->orderData);
                    if ($result===false) {
                        return false;
                    }   
                }   
            
                
            /**
             * Add the data to Navision by SOAP-Requests
             * @link [Orderdetail Comments incl Styling](https://trello-attachments.s3.amazonaws.com/5d39aa9c39cbe152bdb91be5/5c86df8ed8b5b55dc3e416a1/eaf970cf1e1f3cc4a417522d27ed078b/image.png) 
             */
                $send2navision = (int)($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/send2navision')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($send2navision) {
                    return $this->add2navision();
                } else {
                    $this->addLogMessage("Not sending to Navision, because it is disabled. See Shops -> Konfiguration -> Pixelmechancis/Navision", "debug");
                }
                
            return true;
            // return $filename;
        }
     

    /**
     * Main function to export orders.
     *
     * @param string $order_id (Magento order entity ID)
     * */
        public function updateOrder($order_id, $data) {
            
            $webServiceName = "Verkaufsauftrag";
            $this->navHelper = new \Nav($this->navision_url, $webServiceName, $this->navision_login, $this->navision_pwd, $this->navision_soapAction);
                
                
            $this->order_id = $order_id;
            $this->orderData = null;
                
            // Check if an order with this id was found and loaded sucessfully.
                $this->getOrderByID($order_id);
                if (!$this->order->getId()) {
                    $this->_logger->error('Order. # '. $order_id. 'exists, but could not be loaded from Magento.');
                    return;
                }

            // Get the order's increment id
                    $this->order_increment_id = $this->order->getIncrementId();
                
            // Convert the Order-Datails from Magento to exportable values.
                    $this->generateOrderdataArray($order_id, $this->order_increment_id);                
                
            // Navision-Data is stored as a file currenly. PM RH
                $navData = $this->getNavisionDataForOrderID($order_id);
                if (!is_array($navData) || empty($navData) || !isset($navData["No"])) {
                    $msg = "Cannot update the Sales-Order, because no Sales-NO. was found for Order-ID $order_id";
                    $this->addLogMessage($msg, "debug");
                    return false;
                }
                
                
                // $key = $navData["Key"];
                $no = $navData["No"];
                $this->addLogMessage("Updateing Sales-Order #$no in Navision with this data: ".print_r($data, 1), "debug");
                
                try {
                    $data = $this->addStatusToVerkaufsauftrag($data);
                } catch (\Exception $e) {
                    preprint($e->getMessage(), __FILE__.__LINE__);
                }
                // preprint($navData, __FILE__.__LINE__); preprint($data, __FILE__.__LINE__); die();
                
                $result = $this->navHelper->updateVerkaufsauftrag($navData, $data);
                if (ENVIRONMENT!="production") {
                    // preprint($data, __FILE__.__LINE__); preprint($navData, __FILE__.__LINE__); die();
                    $this->addLogMessage("UpdateOrder Result: ".print_r($result, 1), "debug");
                }
                
            return true;
            // return $filename;
        }
    
    
    
    
    
    /**
     * Save data of order and customer in array $this->orderData. This is used for the creation of the XML file later.
     * Also creates some custom-keys with concatenated data, like the shipping-adress and so on.
     */
        private function generateOrderdataArray($order_id, $order_increment_id) {
            
            // Get customer data from order
                $customer_id = $this->order->getCustomerId();                
                $isGuest = $this->order->getCustomerIsGuest();

            // Create a date object of the Magento order date to format it later
                $order_date = date_create($this->order->getCreatedAt());

            // Subtotal (Total without tax and shipping costs), rounded to 2 deciamals
                $order_subtotal = $this->order->getSubtotal() + $this->order->getDiscountAmount();
                $rounded_subtotal = $this->_exporthelper->formatPrice($order_subtotal);

            // Order store info
                $order_store = $this->order->getStoreName();

            // Check if order as a coupon code or not
                if (!empty($this->order->getCouponCode())) {
                    $this->used_coupon = true;
                }
                
               
            /**
             * Submit the Host-Name (www.engelsrufer.de?) and IP-Adress from that this export was called.
             * It is NOT the IP and Host of the User, who placed the order.
             */
                $host = "unknown/cli";
                $ip = "unknown/cli";
                if (isset($_SERVER["HTTP_HOST"])) {
                    $host = $_SERVER["HTTP_HOST"];
                    $ip = $_SERVER["REMOTE_ADDR"];
                }
                // Should be defined in /defines.php every time.
                if (!defined("ENVIRONMENT")) {
                    define("ENVIRONMENT", "unknown/cli");
                }
                
                
            /**
             * Save data of order and customer in array $this->orderData. This is used for the creation of the XML file later.
             */
                $this->orderData = array(
                    "Host" => $host,
                    "IP" => $ip,
                    "Pixmex-Environment" => ENVIRONMENT,
                    // "order_data" => $this->order->getData(), // Coupon Code
                    "Order Type" => 'Online', // Order type (always 'Online')
                    "Order Status" => $this->order->getStatus(),  // processing? Complete? Pending?
                    "Order Store" => $order_store, // Storeview
                    "Order Date" => date_format($order_date, 'd.m.y'), // Date the order was placed
                    "Order Increment Id" => $order_increment_id, // Order increment id
                    
                    "Subtotal" => $rounded_subtotal, // Subtotal (Total without tax,discounts and shipping)
                    "Grand Total" => $this->order->getGrandTotal(), // Grand total
                    "Coupon Code" => $this->order->getCouponCode(), // Coupon Code 
                    "Coupon Value" => $this->order->getBaseDiscountAmount(), // Coupon Code Amount: https://magento.stackexchange.com/a/260832
                    "Discount Amount" => $this->order->getDiscountAmount(), // Gift-Card Data? See https://trello.com/c/bXjkJ32I/66-2020-02-einl%C3%B6sen-eines-gutscheins-mit-anderem-sachkonto-f%C3%BCr-navision#comment-5eb02ffd8ebf8c826ce9804f and https://bitbucket.org/pixelmechanics/engelsrufer-relaunch/pull-requests/626/feature-20200218-giftcard-export-rh/diff#comment-148221589
                    
                    "Customer isGuest" => $isGuest, // 0 or 1, if order placed as GUEST User
                    "Customer Id" => $customer_id, // Magento customer id
                    "Customer Email" => $this->order->getCustomerEmail(),
                );
               
                
                
            /**
             * Kundennummer kommt aus Navision. Der Einfachheit halber generieren wir Sie mit "BC1000000" und addieren die Magento ID dazu, dann sollte hier kein Problem entstehen.
             * PM RH 25.11.2019: Da wir die Debitor-ID direkt bei der Order abspeichern und nicht mehr beim Magento-Kunden, ist diese Meldung nicht mehr relevant.
             * @todo: Lieber die Order prüfen, ob hier erp_id oder salesorder_id vorhanden sind.
             */
                if ($customer_id>0) {
                    
                    $customer = $this->_customerRepositoryInterface->getById($customer_id);
                    $erp_id = $this->customerGetERPID($customer_id); 
                    
                    // $this->orderData["customer_data"] = $customer->getData(); 
                    $this->orderData["customer_erp_id"] = $erp_id; 
                    
                } else {
                    // "Kein Customer mit der ID '$customer_id' gefunden. $erp_id";
                    // $msg = "Gast-Bestellung, kann Debitor-ID nicht mit Magento-Customer verknüpfen.";
                    // $this->addLogMessage($msg, "debug");
                }
                

            /**
             * Get the Payment Transaction-ID
             * PM RH 18.10.2019
             * @todo Check if this is the Transaction ID from the payment-provider.
             * @link https://magento.stackexchange.com/a/268195
             * @link https://stackoverflow.com/a/38520460
             */
                $transaction = $this->transXInterface->create()->addOrderIdFilter($order_id)->getFirstItem();
                $transactionId = $transaction->getData('txn_id');
                $txData = $transaction->getData();
                $this->orderData["Payment Transaction-ID"] = $transactionId;
                $this->orderData["State"] = $this->order->getState(); // processing? Complete? Pending?
                
                try {
                    $payment = $this->order->getPayment();
                    if ($payment) {
                        $this->orderData["Payment Code"] = $payment->getMethod(); // Comes from Magento, something like "checkmo", "paypal_express", ...,
                        $this->orderData["Payment State"] = $payment->getState(); // @link https://magento.stackexchange.com/a/177174
                        $this->orderData["Payment_data"] = $payment->getData(); // @link https://magento.stackexchange.com/a/177174
                    }
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    if (ENVIRONMENT!="production") {
                        $message .= "\n order->getPayment() konnte nicht ausgeführt werden. Siehe ".__FILE__.__LINE__;
                    }
                    $this->addLogMessage($message, "debug");
                }
                    
                $this->orderData["txData"] = $txData;
                

            /**
             * Get billing address and its whole customer name (prefix, first and last name, company)
             */
                $billing_address = $this->order->getBillingAddress();
                if ($billing_address) {
                    $billing_wholename = implode(" ", array($billing_address->getPrefix(), $billing_address->getFirstname(), $billing_address->getMiddlename(), $billing_address->getLastname())); // Company gehört in Name_2
                    $billing_wholename = trim(str_replace("  ", " ", $billing_wholename)); // doppelte Leerzeichen durch einfache ersetzen
                    $billing_street = implode(',', $billing_address->getStreet());
                    $this->orderData = array_merge($this->orderData, array(
                        "Customer Name Full" => $billing_wholename, // Whole name of the customer
                        "Customer Prefix" => $billing_address->getPrefix(),
                        "Customer Firstname" =>  $billing_address->getFirstname(),
                        "Customer Lastname" =>  $billing_address->getLastname(),
                        "Customer Company" =>  $billing_address->getCompany(),
                        
                        "billing_data" => $billing_address->getData(),
                        "Billing Name" => trim($billing_wholename), // Billing name
                        "Billing Phone" => $billing_address->getTelephone(), // Billing name
                        "Billing Middlename" => trim($billing_address->getMiddlename()), // Billing Middlename
                        "Billing Address" => $billing_street, // Billing street
                        "Billing PostCode" => $billing_address->getPostcode(), // Billing postocde
                        "Billing City" => $billing_address->getCity(), // Billing city
                        "Billing Country" => $billing_address->getCountry(), // Billing country
                        "Billing Country Id" => $billing_address->getCountryId(), // Billing country Code
                    ));
                }
                
                
                
            /**
             * Shipping Methods and price
             */
                $shippingMethod = '';
                $shippingPrice = '';
                if($this->order->getShippingDescription()){
                    $shippingMethod = $this->order->getShippingDescription();
                    $shippingPrice = $this->_exporthelper->formatPrice($this->order->getShippingAmount());
                    $this->orderData = array_merge($this->orderData, array(
                        "Shipping Method" => $shippingMethod, //Shipping Method
                        "Shipping Price" => $shippingPrice, // Shipping costs
                    ));
                }
                
            /**
             * Get shipping address and its whole customer name (prefix, first and last name, company)
             */
                $shipping_wholename = '';
                $shipping_street = '';
                $shipping_countryid = '';
                $shipping_postcode = '';
                $shipping_city = '';
                $shipping_phone = '';
                $shipping_prefix = '';
                $shipping_firstname = '';
                $shipping_lastname = '';
                $shipping_company = '';
                
                try {
                    
                    $shipping_address = $this->order->getShippingAddress(); 
                    // There is no shipping adress, when only digital-products where bought, example: gift-voucher
                    if($shipping_address) {
                        $shipping_wholename = implode(" ", array($shipping_address->getPrefix(), $shipping_address->getFirstname(), $shipping_address->getMiddlename(), $shipping_address->getLastname())); // Company gehört in Name_2
                        $shipping_wholename = trim(str_replace("  ", " ", $shipping_wholename)); // doppelte Leerzeichen durch einfache ersetzen
                        $shipping_street = implode(',', $shipping_address->getStreet());
                        $shipping_countryid = $shipping_address->getCountryId();
                        $shipping_postcode =  $shipping_address->getPostcode();
                        $shipping_city = $shipping_address->getCity();
                        $shipping_phone = $shipping_address->getTelephone(); // https://magento.stackexchange.com/a/30199
                        $shipping_prefix = $shipping_address->getPrefix();
                        $shipping_firstname = $shipping_address->getFirstname();
                        $shipping_lastname = $shipping_address->getLastname();
                        $shipping_company = $shipping_address->getCompany();
                        
                        $shippingData = array(
                            // "shipping_data" => $shipping_address->getData(),
                            "Shipping Name" => trim($shipping_wholename), // Shipping Name
                            "Shipping Prefix" => trim($shipping_prefix),
                            "Shipping Firstname" => trim($shipping_firstname),
                            "Shipping Lastname" => trim($shipping_lastname),
                            "Shipping Middlename" => trim($shipping_address->getMiddlename()),
                            "Shipping Company" => trim($shipping_company),
                            "Shipping Phone" => trim($shipping_phone),
                            "Shipping Address" => $shipping_street, // Shipping Street
                            "Shipping Postcode" => $shipping_postcode, //Shipping Postcode
                            "Shipping City" => $shipping_city, //Shipping City
                            "Shipping county id" => $shipping_countryid, //shipping Country    
                        );
                        $this->orderData = array_merge($this->orderData, $shippingData);
                    }
                // but we need to log this as a message
                    else {
                        $message = "Order $order_id ($order_increment_id) has no Shipping Adress. Perhaps it is a GIFT-Voucher?";
                        $this->addLogMessage($message, "debug");
                    }

                    
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                    if (ENVIRONMENT!="production") {
                        $msg .= "\n order->getShippingAddress() konnte nicht ausgeführt werden. Siehe ".__FILE__.__LINE__;
                    }
                    $this->addLogMessage($msg, "error");
                }
				/**
				 * Get Amasty Gift Card Details 30/04/2020 gift card export PV
                 * @link https://trello.com/c/bXjkJ32I/66-2020-02-einl%C3%B6sen-eines-gutscheins-mit-anderem-sachkonto-f%C3%BCr-navision#comment-5e6f765b6331f9880fde03a8
				 **/				
                    $gift_card_code = '';
                    $gift_card_amount = '';
                    $gift_card_base_amount = '';
                    $gift_card_id = '';
                    $gift_card_account_id = '';

                    try { 
                        // $extension_attribute = $this->order->getExtensionAttributes();  
                     
                        /**
                         * get amasty gift card details from order
                         * PM AJ/VK 08.03.2022
                         * Note: need to update below code, if Amasty update its code
                         * Trello - https://trello.com/c/fGjI4iPU/280-probleme-beim-export
                         */
                        $gCardOrder = $this->order->getExtensionAttributes()->getAmGiftcardOrder();
                        $giftCards = $gCardOrder->getGiftCards();        
                        if ($giftCards) {  
                                             
                            foreach ($giftCards as $card) {

                                $gift_card_code = $card['code'];
                                $gift_card_amount = $card['amount'];
                                $gift_card_account_id = $card['id'];
                                $gift_card_base_amount = $card['b_amount'];
                            }
                            $gift_card_id = $gCardOrder->getId();
                           
                            /* 
                            $gift_card_code = $extension_attribute->getAmgiftcardCode();
                            $gift_card_amount = $extension_attribute->getAmgiftcardGiftAmount();
                            $gift_card_base_amount = $extension_attribute->getAmgiftcardBaseGiftAmount();
                            $gift_card_id =  $extension_attribute->getAmgiftcardCodeSetId();
                            $gift_card_account_id = $extension_attribute->getAmgiftcardAccountId();
                            */              
                            $giftData = array(                            
                                "Gift Card Code" => trim($gift_card_code),
                                "Gift Card Amount" => trim($gift_card_amount),
                                "Gift Card Base Amount" => trim($gift_card_base_amount),
                                "Gift Card Id" => trim($gift_card_id),
                                "Gift Card Account Id" => trim($gift_card_account_id),                               
                            );
                            $this->orderData["GiftCard"] = $giftData;
                           
                        }
                    // but we need to log this as a message
                        else {
                            $message = "Order $order_id ($order_increment_id) has no Gift Card Data?";
                            $this->addLogMessage($message, "debug");
                        }

                    
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                    if (ENVIRONMENT!="production") {
                        $msg .= "\n order->getExtensionAttributes() konnte nicht ausgeführt werden. Siehe ".__FILE__.__LINE__;
                    }
                    $this->addLogMessage($msg, "error");
                }
				
            /**
             * Evtl Lieferung an Packstation von Sendcloud mit diesen Werten holen?
             * @todo
             * / 
                $this->orderData["sendcloud_service_point_id"] = $this->order->get("sendcloud_service_point_id");
                $this->orderData["sendcloud_service_point_name"] = $this->order->get("sendcloud_service_point_name");
                $this->orderData["sendcloud_service_point_street"] = $this->order->get("sendcloud_service_point_street");
                $this->orderData["sendcloud_service_point_house_number"] = $this->order->get("sendcloud_service_point_house_number");
                $this->orderData["sendcloud_service_point_zip_code"] = $this->order->get("sendcloud_service_point_zip_code");
                $this->orderData["sendcloud_service_point_city"] = $this->order->get("sendcloud_service_point_city");
                $this->orderData["sendcloud_service_point_country"] = $this->order->get("sendcloud_service_point_country");
                /**/
                
                
                 
            /**
             * the Kundennummer from Navision
             * @link https://trello.com/c/T1bCYqVB/31-api-09-order-export-magento-navision-debitor-navision-teil-2
             */
                if (isset($this->orderData["erp_id"])) {
                   $debitor["E_Mail"] = $this->orderData["Customer Email"];
                }

                $msg = __FILE__.__LINE__.", ".print_r($this->orderData, 1);
                $this->orderLog($order_id, $msg);
                
            // PM RH: use this line, to see the generated Data before it is being sent to navision
                // preprint($this->orderData, "orderData, ".__FILE__.__LINE__); preprint($this->order->getBaseSubtotalWithDiscount(), "getBaseSubtotalWithDiscount, ".__FILE__.__LINE__); die();
                
        } // generateOrderdataArray();
        
        
        
    /**
     * Gets the ERP-ID from navision into the custom customer-attribute
     * @link https://trello.com/c/ytbOL9Aw/291-prio-1-new-customer-numbers-erp-id#comment-5dc3eb2c1005d233c1b46635
     * @param type $customer_id
     * @param type $debitorNo
     */
        public function customerGetERPID($customer_id) {
            $erp_id = false;
            
            // anhand neuem Feld erp_id
                $customer = $this->_customerRepositoryInterface->getById($customer_id);
                $customer->getCustomAttribute("erp_id");
                $erpAttribute = $customer->getCustomAttribute("erp_id");
                if ($erpAttribute) {
                    $erp_id = $erpAttribute->getValue(); // Wir speichern das erstmal hier drin ab, weil das Feld nirgendwo benutzt wird.
                    $msg = "Lade ERP-ID aus ERP-ID-Attribut für Customer-ID $customer_id: $erp_id";
                    $this->addLogMessage($msg, "debug");
                }
                // Fallback: anhand Suffix
                else {
                    $erp_id = $this->order->getCustomerSuffix();
                    $msg = "Lade Erp-ID aus SUFFIX-Attribut für Customer-ID $customer_id: $erp_id";
                    $this->addLogMessage($msg, "debug");
                }
                
            return $erp_id;
        }
        
        
    /**
     * Saves the ERP-ID from navision into the custom customer-attribute
     * @link https://trello.com/c/ytbOL9Aw/291-prio-1-new-customer-numbers-erp-id#comment-5dc3eb2c1005d233c1b46635
     * @param type $customer_id
     * @param type $debitorNo
     */
        public function customerSetERPID($customer_id, $debitorNo) {
            $msg = "Speichere Debitor-ID zu Magento-Customer ab: customerSetERPID($customer_id, $debitorNo)";
            $this->addLogMessage($msg, "debug");
            
            
            $customer = $this->_customerRepositoryInterface->getById($customer_id);
                $customer->setCustomAttribute('erp_id', $debitorNo);
                $customer->setSuffix($debitorNo); // Fallback
            $return = $this->_customerRepositoryInterface->save($customer);
            
            $message = "Magento Customer-ID $customer_id erhält im Feld ERP-ID die NAVISION-No $debitorNo";
            $this->addLogMessage($message, "success");
            
            return $return;
        }
            
    
    
    /**
     * Add a message to the order-comments, output it via the message-manager and also write it to the LOG in case of errors.
     * PM RH 21.10.2019, used by the methods create-XML and sendToNavision
     * 
     * @link https://www.rakeshjesadiya.com/display-success-and-error-messages-using-magento-2/
     * @link https://stuntcoders.com/snippets/magento-programmatically-add-comment-to-order/ and https://webkul.com/blog/add-custom-comment-programmatically-to-order-in-magento-2/
     * @param type $message String the message.
     * @param type $this->order Magento ORDER-Object
     * @param type $type error, notice, success, warning
     */
        protected function addLogMessage($message, $type="error") {
            
            
            // $status = $this->order->getStatus(); // no custom status posssible for these history-messages like "Order-Export XML finished"
            // $this->order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $message = strtoupper($type).":: ".$message;
            $this->order->addStatusHistoryComment($message)->setIsCustomerNotified(false)->save(); // Add the comment and save the order (last parameter will determine if comment will be sent to customer)

            /**
             * the Frontend-Customer does not understand these messages and they are only for the backend, where an Admin can manually run the order-export.
             * Addon 12.12.2019: In CLI-Mode we want to output messages.
             * For Debugging in DEV Mode, we should output them
             */
                if ($type!=="debug" && (php_sapi_name() == 'cli' || isset($_SERVER["HTTP_POSTMAN_TOKEN"]) ) )  {
                    print_console($message, $type);
                    return;
                }
                elseif ($this->is_frontend) { // && ENVIRONMENT!="development") {
                    $this->_logger->error($message);
                    $type = "debug";
                    return true;
                }
                
            
            switch ($type) {
                case "error":   $this->_logger->error($message);
                                $this->messageManager->addErrorMessage($message);
                                break;

                case "notice":  $this->messageManager->addNoticeMessage($message); break;
                case "warning": $this->messageManager->addWarningMessage($message); break;
                case "success": $this->messageManager->addSuccessMessage($message); break;
                case "debug":   // only output the message, if in development-mode (not magento, see /defines.php)
                                if (defined("ENVIRONMENT") && ENVIRONMENT=="development") {
                                    $this->messageManager->addNoticeMessage("DEV-OUTPUT: ".$message);
                                }
                                break;
            }

            return true;
        }
        
        
        
        
        
        
        
/********************* XML-Stuff **************************/
        



    /**
     * Function to create the XML file.
     * @param object $this->order
     * @param array $xml_basic_data
     **/
        protected function createXmlFile($xml_basic_data) {
            
            $currentDate = date("Ymd");
            // Get the directory path of exported orders. Create the directory if not exists with writing permissions.
            $exportDirectory = $this->_exporthelper->getOrderExportDirectoryPath();
            if (!file_exists($exportDirectory)) {
                mkdir($exportDirectory, 0644, true); // should be 0644 and not 0777.
            }
            $filename = $this->_exporthelper->getFilenameOfOrderExport($exportDirectory, $currentDate, $this->order_increment_id);
            
            // Create XML object
                $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Order></Order>');

            // Setup the basic structure for the XML file
                $RecordSet = $xml->addChild('RecordSet');
                $Record = $RecordSet->addChild('Record');
                $Record->addAttribute('Action', 'NEW');

            /**
             * Add fields to XML - Basic customer and order infos
             * */
                foreach ($xml_basic_data as $key => $_xml_basic_data) {
                    // Skip entry if empty
                    if (!$_xml_basic_data) {
                        continue;
                    }

                    // Create fields for the XML file for each data entry
                    $Field = $this->addCdataXML('Field', $_xml_basic_data, $Record);
                    $Field->addAttribute('name', $key);

                }       

            /**
             * Add fields to XML - Ordered Items
             **/
                // Get all orderes items
                $order_items = $this->order->getAllVisibleItems();
                $order_item_count = 0;

            // Loop through ordered items
                foreach ($order_items as $item) {
                    // Get item sku
                    $item_sku = $item->getSku();
                    $item_name = $item->getName();
                    $item_price = $this->_exporthelper->formatPrice($item->getPrice());
                    $item_qty = $item->getQtyOrdered();
                    $item_totalPrice = $item->getRowTotal();
                    $xml_item_data = array(
                        "Sku" => $item_sku, // SKU
                        "Name" => $item_name, // Name
                        "Price" => $item_price, //Price
                        "Qty" => $item_qty, // ordered qty
                        "Total Price" => $item_totalPrice
                    );
                    $this->addProductRowXML($xml_item_data, $order_item_count, $Record); 
                }

            /**
             * Save XML file
             **/
                // Use Dom to format the XML file nicely. Unfortunately this doesn't work with SimpleXML.
                $dom = new \DOMDocument('1.0', 'UTF-8');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->loadXML($xml->asXML());
                $dom->save($filename);

            // Check if file was created, log and return if not
                if (!file_exists($filename)) {
                    $message = "Fehler im Order-Export: XML-Datei {$filename} für Order-ID: ".$this->order->getIncrementId()." nicht erstellt.";
                    $this->addLogMessage($message, "notice") ;
                    return false;
                }

            // Log order export sucess.
                $customer_info = "Customer: " . $xml_basic_data['Customer Name Full'];
                $sucessmsg = "Order #".$this->order-> getIncrementId()." was successfully exported from Magento. ". $customer_info;
                $this->_logger->info($sucessmsg); // Logmessage




            // @see convertByte() in /pm_helper.php
            $message = "Order-Export: XML-Datei erstellt: {$filename} (".convertByte(filesize($filename)).")";
            $this->addLogMessage($message, "notice") ;

            return $filename;
        } // _createXMLFile()
    





    /**
     * Function to format strings with Cdata. This is used for the creation of the XML file.
     * @param string $name
     * @param string $value
     * @param object $parent
     * */
        public function addCdataXML($name, $value, &$parent) {
            $child = $parent->addChild($name);

            if ($child !== NULL) {
                $child_node = dom_import_simplexml($child);
                $child_owner = $child_node->ownerDocument;
                if (is_string($value)) {
                    $child_node->appendChild($child_owner->createCDATASection($value));                    
                }
            }

            return $child;
        }
    
     /**
     * Function to add one product with all its fields to the XML file.
     *
     * @param array $xml_item_data (array with all the product data)
     * @param int $order_item_count (counts the amount of positions of this order)
     * @param object $Record (object from the XML file)
     **/
        public function addProductRowXML($xml_item_data, $order_item_count, $Record) {
            $Row = $Record->addChild('Row');
           // Add fileds in xml
            $Field = $this->addCdataXML('Field', $xml_item_data['Sku'], $Row);
            $Field->addAttribute('name','Sku');
            $Field = $this->addCdataXML('Field', $xml_item_data['Name'], $Row);
            $Field->addAttribute('name','Name');
            $Field = $this->addCdataXML('Field', $xml_item_data['Price'], $Row);
            $Field->addAttribute('name','Price');
            $Field = $this->addCdataXML('Field', $xml_item_data['Qty'], $Row);
            $Field->addAttribute('name','Qty');
            $Field = $this->addCdataXML('Field', $xml_item_data['Total Price'], $Row);
            $Field->addAttribute('name','Total Price');
        }
    
        
        
/************************** NAVISION Stuff ******************************/    
        
        
        
        
    /**
     * Send the information to Navision directly via SOAP
     * @param MagentoOrder? $this->order 
     */
    public function add2navision() {
        
        /**
         * Create the Debitor first.
         */
            $debitor = $this->exportDebitorToNavision();
            // preprint($debitor, __FILE__.__LINE__); die();
            if ($debitor==false) {
                return false;
            }
                
                
        // So now we can save the Debitor-No to the Mage-Customer
            $debitorID = $this->storeDebitorID($debitor, $this->order);
                
                
                
        /**
         * Now add an AUFTRAG for the Debitor
         */            
            $auftrag = $this->exportAuftragToNavision($debitorID);            
            if ($auftrag==false) {
                return false;
            }
        
        /**
         * Now add the Navision-IDs to the Magento-Order
         * @link https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing
         */
            $data = array(
                "salesorder_no" => $auftrag["No"],
                "debitor_no" => $debitorID,
            );
            $this->saveNavisionDataForOrderID($this->order_id, $data);
            // $auftragID = $this->addAuftragIDtoOrder($this->order_id, $auftrag);

        return $auftrag["No"];
    }
    
    
    
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
     * Creates a Debitor in Navision and returns the Data from Navision
     * @return array Data from Navision
     **/
        public function exportDebitorToNavision() {
                    
            $webServiceName = "Debitor";
            $this->debitor = $this->nav_getDebitorFromOrder();
			
            $this->navHelper = new \Nav($this->navision_url, $webServiceName, $this->navision_login, $this->navision_pwd, $this->navision_soapAction);
            $result = false;
            try {
                $result = $this->navHelper->createDebitor($this->debitor);				
             } catch (\Exception $e) {
                $this->addLogMessage($e->getMessage(), "debug"); 
                $this->addLogMessage($this->navHelper->error, "warning"); 
                // preprint($result, "Debitor aus NAV, ".__FILE__.__LINE__);
            }
            
            // if it was not created, we cannot continue.
                if ($result===false || !is_array($result) || !isset($result["No"])) {
                    
					if (ENVIRONMENT=="development") {
						preprint($this->debitor, __FILE__.__LINE__); preprint($result, __FILE__.__LINE__); preprint($this->navHelper->error, __FILE__.__LINE__); die();
					}				
					
                    if (!isset($this->debitor["erp_id"]) || empty($this->debitor["erp_id"])) {
                        
                        if (ENVIRONMENT=="development") {
                            $this->addLogMessage("Result is not an array or does not contain result[No]. Result=".print_r($result, 1).".", "debug"); 
                            $this->addLogMessage("Export DEBITOR to Navision {$this->navision_url} failed widh this data: ".print_r($this->debitor, 1).".", "debug"); 
                        }
                        $this->addLogMessage($this->navHelper->error, "error"); 
                        return false;
                    } 
                    
                    else {
                        $msg = "Verwende ERP-ID des Customers ohne den Debitoren zu updaten: ".$this->debitor["erp_id"];
                        $this->addLogMessage($msg, "warning"); 
                        $result = $this->debitor;
                        $result["No"] = $this->debitor["erp_id"];
                    }
                    
                }
                
        
            // Debitor was added and we got the ID (Field "No")
                $debitor = $result;
                $message = "Export DEBITOR from Order {$this->order_increment_id} to Navision {$this->navision_url} SUCCESS: ".$result["No"]."\n\n";
                $this->addLogMessage($message, "success"); 
                
                $message = "Export DEBITOR  to Navision {$this->navision_url} Debugging Data:\n\n";
                $message .= "Result = ".print_r($result, 1)."\n\n";
                $message .= "Request-Data = ".print_r($this->debitor, 1);
                $this->addLogMessage($message, "debug"); 
        
            
            /**
             * Diese Zeilen nur ausführen, wenn Debitor NEU angelegt wurde in NAV 
             */
                if (!isset($debitor["isUpdated"]) || $debitor["isUpdated"]!==true) {
                    /**
                     * Auflistung der bestellten Produkt-Gruppen
                     * KT steht immer dabei, bedeutet Ketten
                     * ER = Engelsrufer
                     * HE = Herzengel
                     * ERWA = Uhren, usw.
                     * ist also individuell was der Kunde eben bestellt.
                     * Leider muss man das bisher manuell eingeben. (bis November 2019)
                     * Man muss quasi auf die Bestellung schauen und dann die Kürzel für die Produkte eingeben.
                     * Den Reiter zum eingeben findet man du unter Debitor - Navigate - Artikelkategorien
                     * 
                     * Erstmal feste alle, die es im Magento geben soll. Besprochen mit RH/Nicky am 31.10.2019, da aktuell nicht anders machbar
                     * @link https://trello.com/c/c6NYHOLi/39-order-export-create-debitor-mit-artikelkategorien
                     * Im backend einstellbar: pmadmin/admin/system_config/edit/section/pixelmechanics_configuration/
                     * @link https://trello.com/c/c6NYHOLi/39-order-export-create-debitor-mit-artikelkategorien-customer-item-categories#action-5dc5777bbce55730e1a1becc
                     */
                        $productGroups = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/debitorartikelgruppencodes'));
                        if (!empty($productGroups)) {
                            
                            $result = $this->navHelper->addCustomerItemCategories($debitor["No"], $productGroups);
                            if ($result===false) { // Meistens beim Update eines existierienden Debitors.
                                $this->addLogMessage($this->navHelper->error, "error"); 
                            } else {
                                $message = "addCustomerItemCategories-Result = ".print_r($result, 1)."\n\n";
                                $this->addLogMessage($message, "debug"); 
                            }
                            
                        } else {
                            $message = "Bitte noch die Debitor-Artikelgruppencodes eintragen in: Shops -> Konfiguration -> Pixelmechancis/Navision -> Debitor Artikelgruppencodes.";
                            $this->addLogMessage($message, "error"); 
                        }
                    
                // dem neuen Debitor IMMER das hier hinzufügen, damit das danach im Verkaufsauftrag ausgewählt werden kann.
                    $this->navHelper->addStdCustSalesCode($debitor["No"], "FRACHT B2C");
                }
                
            return $debitor;
        }
        
        
        
        
    /**
     * Creates a Verkaufsauftrag in Navision for a DebitorID and returns the Data from Navision.
     * @return array Data from Navision
     * @link Field-Definitions from Navision: https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit Europe GmbH/Page/Verkaufsauftrag
     **/
        public function exportAuftragToNavision($debitorID) {
            
            // $this->addLogMessage("Starte exportAuftragToNavision($debitorID)", "debug"); 
            $auftrag = $this->nav_getauftragFromOrder($debitorID);
            // echo "<pre>"; print_r($auftrag); echo "</pre>"; die();
            
            /** /
            preprint($auftrag, "Lege Verkaufsautrag an für debitornr: {$debitorID}, ".__FILE__.__LINE__);
            preprint($this->orderData["Shipping Price"], "Shipping checken: {$debitorID}, ".__FILE__.__LINE__);
            // Gift-Card Infos: https://trello.com/c/SqZE9KHX/27-gift-cards-f%C3%BCr-navision#comment-5e4bf2576287f14ad3f86d46
            preprint($this->orderData["GiftCard"], "Gift Card?, ".__FILE__.__LINE__);
            preprint($this->orderData, __FILE__.__LINE__);
            die();
            /**/
            
            $result = false;
            try {
                $result = $this->navHelper->createVerkaufsauftrag($debitorID, $auftrag, $this->orderData);
            } catch (\Exception $e) {
                $this->addLogMessage($e->getMessage(), "debug"); 
                $this->addLogMessage($this->navHelper->error, "error"); 
                // preprint($result, "Result: {$debitorID}, ".__FILE__.__LINE__); die();
            }
            
            // if it was not created, we cannot continue.
                if ($result===false || !is_array($result)) {
                    // preprint($result, "Result is not an array or does not contain result[No] in ".__FILE__.__LINE__); preprint($auftrag, "Debitor for navision in ".__FILE__.__LINE__); die();
                    $this->addLogMessage("Export AUFTRAG from Order {$this->order_increment_id} to Navision {$this->navision_url} failed widh this data: Auftrag = ".print_r($auftrag, 1).".", "error"); 
                    $this->addLogMessage($result, "debug"); 
                    $this->addLogMessage($this->navHelper->error, "error"); 
                    // preprint($result, "Result für DebitorID: {$debitorID}, ".__FILE__.__LINE__); die();
                    return false;
                }
                
            // Debitor was added and we got the ID (Field "No")
                $message = "Export AUFTRAG from Order {$this->order_increment_id} to Navision {$this->navision_url} SUCCESS: {$result['No']}\n\n";
                $this->addLogMessage($message, "success"); 
                
                $message = "Export AUFTRAG to Navision {$this->navision_url} Debugging Data:\n\n";
                $message .= "Result = ".print_r($result, 1)."\n\n";
                $message .= "Request-Data = ".print_r($auftrag, 1);
                $this->addLogMessage($message, "debug"); 
            
            
            return $result;
        }
        
        
    
    
    
    /**
     * Save the Debitor-No to the order, so an new order-export already knows, that this debitor exists.
     * @todo: How and where could we store the No? Perhaps we do not need this?
     * @param array $navDebitor, example: array("No" => "100026", "Name" => "Name of the debitor", ...)
     * @param type $mageOrder magento-Order Object.
     * @return type
     */
        private function storeDebitorID($navDebitor, $mageOrder) {
            
            
            // preprint($navDebitor, __FILE__.__LINE__);
                $debitorID = "";
                if (isset($navDebitor["No"])) {
                    $debitorID = $navDebitor["No"]; // example: "100026"
                }
            
            
            // If the DEBITOR was only updated, we already have the ERP-ID and do not need to save it.
                if (isset($debitor["isUpdated"]) && $debitor["isUpdated"]==true) {
                    return $debitorID;
                }
                
            /**
             * Gast-bestellungen können keinem magento Customer zugewiesen werden.
             */
                $customer_id = $this->orderData["Customer Id"]; // $mageOrder["Customer ID"]
                if (empty($customer_id)) {
                    // preprint($mageOrder, $debitorID.__FILE__.__LINE__); die();
                    return $debitorID;
                }
            
            /**
             * Add BC-Number to the Customer
             */
                try {
                    $this->customerSetERPID($customer_id, $debitorID);
                    if (isset($navDebitor["isUpdated"])) {
                        $message = "Navision-Kunde wurde GEUPDATET mit Magento-Daten.";
                        $this->addLogMessage($message, "debug");
                    }
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    $message .= " - tryed to update Customer with Erp-ID in ".__FILE__.__LINE__;
                    $this->addLogMessage($message, "error");
                }
                
                
            /**
             * Add BC Number to the Order
             * @link https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing
             */
                $data = array(
                    "debitor_no" => $debitorID,
                );
                $this->saveNavisionDataForOrderID($this->order_id, $data);
                


            return $debitorID;
        }
        
        
        
    
    /**
     * Save the Auftrags-No to the order, so an new order-export already knows, that this order previously existed.
     * 
     * @param type $navAuftragnr, example "AT143785"
     * @param MagentoOrder $mageOrder
     * @return type
     * @deprecated Use saveNavisionDataForOrderID() instead
     */
        private function addAuftragIDtoOrder($order_id, $navAuftrag) {
            $navAuftragnr = $navAuftrag["No"];
            $navAuftragKey = $navAuftrag["Key"];
            $navDebitorNo = $navAuftrag["Sell_to_Customer_No"];
            
            $data = array(
                "salesorder_no" => $navAuftragnr,
                "debitor_no" => $navDebitorNo,
            );
            $this->saveNavisionDataForOrderID($order_id, $navAuftrag);

            return $navAuftragnr;
        }
       
    
    
    /**
     * Convert the Order-Data to an array for sending to Navision.
     * Requires $this->orderData.
     * @return type
     * @link See this for field-definitions: https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/Debitor
     */
        private function nav_getDebitorFromOrder() {

            
                
            $fieldsNavMage = array();
            $fieldsNavMage["Phone_No"] = "Billing Phone";
			
           /**
            * Debitor BILLING Fields
            **/
                $fieldsNavMage["Name"] = "Customer Name Full";
                $fieldsNavMage["Bill_to_Name"] = "Billing Name";
                
                $fieldsNavMage["Name_2"] = "Customer Company";
                $fieldsNavMage["Bill_to_Name_2"] = "Customer Company";
                
                $fieldsNavMage["Address"] = "Billing Address";
                $fieldsNavMage["Bill_to_Address"] = "Billing Address";
                // $fieldsNavMage["Bill_to_Address_2"] = "Stockwerk evtl?"; nicht in Magento vorhanden!
            
                // City und PLZ sollen in 1 Zeile, siehe https://trello.com/c/dlLfzz4b/30-debitor-mit-artikelkategorien-anlegen#comment-5dca799c18897c5addcbf61e
                $fieldsNavMage["Post_Code"] = "Billing PostCode";
                $fieldsNavMage["Bill_to_Post_Code"] = "Billing PostCode";
                $fieldsNavMage["City"] = "Billing City";
                $fieldsNavMage["Bill_to_City"] = "Billing City";
                
                $fieldsNavMage["Bill_to_County"] = "Billing County"; // ist das hier die Provinz?
                $fieldsNavMage["Country_Region_Code"] = "Billing Country Id";
                $fieldsNavMage["Bill_to_Country_Region_Code"] = "Billing Country Id";



			
			
            /**
             * the Kundennummer from a previously exported order
             * A New Order should be placed for the same Debitor.
             * But a new debitor might be added in some cases, when Navision would display a confirmation-dialog-box.
             * @link https://trello.com/c/T1bCYqVB/31-api-09-order-export-magento-navision-debitor-navision-teil-2
             */
                if (isset($this->orderData["customer_erp_id"])) {
                   $fieldsNavMage["erp_id"] = "customer_erp_id";
                }
            
            
        /**
         * Assign the Mage-Values to the Debitor-Array and sanitize
         */
            $debitor = array();
            foreach ($fieldsNavMage as $navField => $mageField) {
                if (!isset($this->orderData[$mageField])) {
                    // preprint("this->orderData[$mageField] does not exists", __LINE__);
                    continue;
                }
                
                $value = trim($this->orderData[$mageField]);
                if ($value=="") {
                    // preprint("this->orderData[$mageField] is empty: ".$this->orderData[$mageField], __LINE__);
                    continue;
                }
                
                $debitor[$navField] = $value;
            }

            /**
             * Set the VAT_Bus_Posting_Group 
             * See https://projects.zoho.com/portal/pixelmechanics2#taskdetail/1781812000000381050/1781812000000400001/1781812000000992003
             * PM LB 08/2021
             */
            switch ($this->orderData["Billing Country Id"]) {
                
                //German orders
                case 'DE':
                    $debitor["VAT_Bus_Posting_Group"] = "INLAND";
                    $debitor["Gen_Bus_Posting_Group"] = "INLAND";
                    $debitor["Customer_Posting_Group"] = "INLAND";
                    $debitor["Customer_Price_Group"] = "B2C";
                    $debitor["RRP_Customer_Price_Group"] = "UVP-DE";

                    break;
                
                //Orders from UK
                case 'GB':
                    $debitor["VAT_Bus_Posting_Group"] = "DRITTLAND";
                    $debitor["Gen_Bus_Posting_Group"] = "DRITTLAND";
                    $debitor["Customer_Posting_Group"] = "DRITTLAND";
                    $debitor["Customer_Price_Group"] = "B2C";
                    $debitor["RRP_Customer_Price_Group"] = "UVP-DE";
                    break;
                
                //Orders from other european Countries
                default:
                    $debitor["Gen_Bus_Posting_Group"] = "EU";
                    $debitor["VAT_Bus_Posting_Group"] = "EU_".$this->orderData["Billing Country Id"];
                    $debitor["Customer_Posting_Group"] = "EU";
                    $debitor["Customer_Price_Group"] = "B2C";
                    $debitor["RRP_Customer_Price_Group"] = "UVP-DE";
                    break;
            }
                
            
            
                
               
            /**
             * Das Wort TEST bei diesen 3 Feldern hinzufügen, damit das ganz eindeutig ist, dass es sich um Tests handelt.
             * Denn Test-Shop ist auch an Live-Navision angebunden.
             * @link https://trello.com/c/7yfEDXmg/17-api-09-order-export-magento-navision-verkaufsauftrag-navision-teil-2#action-5dca86bc359a7f27d40ffe5d
             */
                if (ENVIRONMENT!="production") {
                    // $debitor["Name"] = "TEST - ".$debitor["Name"];
                    // $debitor["Address_2"] = implode(", ", array($this->orderData["Host"], $this->orderData["Pixmex-Environment"])); // $this->orderData["IP"]
                }
                
            
            // preprint($debitor, __FILE__.__LINE__); preprint($this->orderData, __FILE__.__LINE__); die();
            return $debitor;
        }


        
        
        
    /**
     * Convert the Order-Data to an array for sending to Navision.
     * Requires $this->orderData.
     * @param $debitor_no = ID of the debitor from Navision. Example: "100033"
     * @return type
     */
        private function nav_getAuftragFromOrder($debitor_no) {
            
            // preprint($this->debitor, __FILE__.__LINE__); die;
            //Get the debitor information for setting the correct VAT Groups
            $this->debitor = $this->nav_getDebitorFromOrder();
            $vatProdPostingGroup = "";

            /**
             * Get the VAT_Prod_Posting_Group
             * See https://projects.zoho.com/portal/pixelmechanics2#taskdetail/1781812000000381050/1781812000000400001/1781812000000992003
             */
                $vatProdPostingGroup = $this->getVatInformation($this->debitor["Country_Region_Code"]);
            

            if (!isset($this->orderData["Payment Transaction-ID"]) || empty($this->orderData["Payment Transaction-ID"])) {
                $this->addLogMessage("Keine Payment Transaktions-ID gefunden für die Bestellung.", "warning"); 
                // preprint($this->orderData, "nav_getAuftragFromOrder($debitor_no)"); die();
            }
            
            /**
             * Die SKU des Flyers kann im backend eingestellt werden.
             * wenn das Feld leer ist, wird kein Flyer hinzugefügt.
             * @example: "DER KUNDENFLYER FS"
             */
                $kundenflyerSKU = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/kundenflyersku'));

            /**
             * Zusätzliche Aktionsartikel, können im Backend eingestellt werden
             * PM LB 12/21
             * https://trello.com/c/GMweMB1a/237-schnittstellen-%C3%A4nderung-zus%C3%A4tzliche-verkaufzeilen
             */
                /**
                 * SKU von Aktionsartikeln
                 */
                    $specialPromotion1Sku = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion1sku'));
                    $specialPromotion2Sku = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion2sku'));
                    $specialPromotion3Sku = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion3sku'));

                /**
                 * Aktionsartikel gültige Länder
                 */
                    $specialPromotion1Countries = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion1countries'));
                    
                    // Convert STRING Input to an Array
                    if (!is_array($specialPromotion1Countries) && !empty($specialPromotion1Countries)) {
                        $specialPromotion1Countries = explode(",", $specialPromotion1Countries); // ergibt: array("DE","GB","AT");
                    }

                    $specialPromotion2Countries = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion2countries'));
                    
                    // Convert STRING Input to an Array
                    if (!is_array($specialPromotion2Countries) && !empty($specialPromotion2Countries)) {
                        $specialPromotion2Countries = explode(",", $specialPromotion2Countries); // ergibt: array("DE","GB","AT");
                    }

                    $specialPromotion3Countries = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion3countries'));
                    
                    // Convert STRING Input to an Array
                    if (!is_array($specialPromotion3Countries) && !empty($specialPromotion3Countries)) {
                        $specialPromotion3Countries = explode(",", $specialPromotion3Countries); // ergibt: array("DE","GB","AT");
                    }

                /**
                 * Aktionsartikel Preisregel
                 */
                    $specialPromotion1PriceRulemin = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion1pricerulemin'));
                    $specialPromotion1PriceRulemax = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion1pricerulemax'));
                    
                    $specialPromotion2PriceRulemin = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion2pricerulemin'));
                    $specialPromotion2PriceRulemax = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion2pricerulemax'));

                    $specialPromotion3PriceRulemin = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion3pricerulemin'));
                    $specialPromotion3PriceRulemax = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/extraSalesLines/specialpromotion3pricerulemax'));

				
			/**
			 * Die SKU des Sachkontos aus Navision, zu dem die Rabatte geschrieben werden
			 * @link https://trello.com/c/SqZE9KHX/27-magento-gift-cards-f%C3%BCr-navision-exportieren-als-salesorderline#comment-5dc180e6ab4bc564203b7f7f
			 **/ 
				$this->giftcardCodeArtno = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/giftcardsku'));
				if (empty($this->giftcardCodeArtno)) {
					$this->giftcardCodeArtno = "1718"; // Default
				}
		
				
                
            $auftrag = array();
            $verkaufsauftragArray = array();

            //VAT_Prod_Posting_Group hinzufügen
                $verkaufsauftragArray = array("VAT_Prod_Posting_Group" => $vatProdPostingGroup);
            
            // Verkaufsauftrag muss einem Debitor zugewiesen sein
                $verkaufsauftragArray = array("Sell_to_Customer_No" => $debitor_no);
            
            // to make sure, that Billing- and Shipping-adresses are also stored int he Salesorder, not only in the debitor.
                $verkaufsauftragArray = $this->addAdressesToVerkaufsauftrag($verkaufsauftragArray);
                
                
            // Depending on the Mage-Order State we have to put different informations in this field. And also consider this on an upate!
                $verkaufsauftragArray = $this->addStatusToVerkaufsauftrag($verkaufsauftragArray);
                
                
            
            
            /**
             * Get all order-items, returns array of all items that are not marked as deleted and do not have a parent item
             * Note that unfortunately, as of magento-2.0 only getItems() is part of the service contract in Magento\Sales\Api\Data\OrderInterface
             * So to get only the configurable product and not its associated product, getAllVisibleItems() is the correct method:
                the single simple item does not have a parent => visible
                the configurable item does not have a parent => visible
                the associated simple item has a parent => not visible
             * @link https://magento.stackexchange.com/a/111123
             **/
                $order_items = $this->order->getAllVisibleItems();
                // preprint($order_items, "order_items, ".__FILE__.__LINE__);
                // $order_items = $this->order->getItems();
                $verkaufsauftragArray["SalesLines"] = array();
                $additionalArticles = array("verpackungen" => array(), "zertifikate" => array()); // add the Verpackungen and Zertifikate at the END, not directly after the Products: https://trello.com/c/aoymfTcT/60-phase-2-sortierung#action-5de6689f92faaa19264ede90
                
                //Order total for extra sales line price rules
                $orderTotal = 0;


                foreach ($order_items as $item) {

                    // Get item sku
                        $sku = $item->getSku();
                        
                    $item_sku = $sku;
                    $item_name = $item->getName();
                    $item_qty = $item->getQtyOrdered();
                    $artnrNAV = strtoupper(trim($item_sku)); // In Navision, the ArtikelNo is always in uppercase. "GIFTCARD" for example.
                    
                    
                    /**
                     * Submit the Final-Price WITH Taxes and WITHOUT Discount.
                     * @todo: Prüfen mit ER, welche Fälle es gibt, und durchspielen, ob es korrekt submitted wurde
                     */
                        $item_totalPrice = $item->getRowTotal();
                        $item_price = $this->_exporthelper->formatPrice($item->getPrice()); // final prices includes discounts, but not taxes
                        // $taxPrice = $this->taxHelper->getTaxPrice($item, $item->getFinalPrice());
                        
                        // //Add Price including VAT to order total
                        $orderTotal += $this->_exporthelper->formatPrice($item->getRowTotalInclTax());

                        $priceInclTax = $this->_exporthelper->formatPrice($item->getRowTotalInclTax());
                        // $priceInclTax = $this->_exporthelper->formatPrice($item->getPriceInclTax());
                    
                    /**
                     * Here we have to set Keys, that Naavision can use.
                     */
                        $productData = array(
                            "VAT_Prod_Posting_Group" => $vatProdPostingGroup,
                            "No" => $artnrNAV, // SKU: ist in Nav immer Uppercase. Bsp "giftcard-100" -> "GIFTCARD-100"
                            "Type" => "Item", // Name
                            "Quantity" => $item_qty, // ordered qty
                            "Unit_Price" => $item_price, //Price ohne Steuer
                            // "VAT_Bus_Posting_Group" => 
                            // "Unit_Price" => $priceInclTax, //Price mit Steuer
                        );
                        

                    /**
                     * bei Giftcards muss das als Sachkonto gespeichert werden https://trello.com/c/kETYQeLO/40-orderexport-giftcards-gutscheine#action-5dc43a933254ee8b8ecf0fff
                     * Deshalb die Produktzeile etwas abändern:
                     */
                        if (strpos($artnrNAV, "GIFTCARD")===0) {						 
							// preprint("TODO: Hier weiter machen", __FILE__.__LINE__); preprint($this->orderData, __FILE__.__LINE__); die();
							
							/**
							 * Info für eCommerce-Manager (Simone)
							 **/
								$descr2 = "Magento Giftcard-ID: '{$item_sku}' ({$priceInclTax} € brutto) "; 
                            

                            $productData["No"] = $this->giftcardCodeArtno;
                            $productData["Type"] = "G_L_Account";
                            $productData["Description"] = strtoupper($item_sku); // wird autom. ersetzt durch navision wegen "No".
                            $productData["Description_2"] = substr($descr2, 0, 50);
                            
                            //Gift cards are not taxed
                            $productData["VAT_Prod_Posting_Group"] = "VAT0";
                        }
                        
                        
                        
                    
                    /**
                     *  Add the product row to the list
                     */
                        $verkaufsauftragArray["SalesLines"][] = $productData;
                        
                        
                        
                    /**
                     * Verpackungen, Flyer und Zertifikate der Bestellung hinzufügen, sofern diese beim Produkt eingetragen wurden.
                     * DAZU muss das magento-Produkt geladen werden, um die Attributswerte auszulesen.
                     * Kommagetrennt sind mehrere Artikelnummern möglich.
                     * 
                     * PM RH 03.12.2019: add the Verpackungen and Zertifikate at the END, not directly after the Products: https://trello.com/c/aoymfTcT/60-phase-2-sortierung#action-5de6689f92faaa19264ede90
                     */
                        $product = $item->getProduct();
                        if (!$product) {
                            $msg = "Produkt '$sku' konnte nicht geladen werden. Verpackungen und Zertifikate können deshalb nicht geladen werden.";
                            $this->addLogMessage($msg, "warning");
                            continue;
                        }
                        $verpackungen = trim($product->getData("verpackungen"));
                        if (!empty($verpackungen)) {
                            $additionalArticles["verpackungen"][] = explode(",", $verpackungen);
                        }
                        
                        $zertifikate = trim($product->getData("zertifikate"));
                        if (!empty($verpackungen)) {
                            $additionalArticles["zertifikate"][] = explode(",", $zertifikate);
                        }
                        
                        
                } // foreach orderitems

            /**
             * Check if special promotion items should be added to the order
             */
                //Promotion item 1
                if(!empty($specialPromotion1Sku)) {
                    if((empty($specialPromotion1PriceRulemin) && $specialPromotion1PriceRulemin !== "0") || ($orderTotal >= $specialPromotion1PriceRulemin && $orderTotal < $specialPromotion1PriceRulemax) || ($orderTotal >= $specialPromotion1PriceRulemin &&  empty($specialPromotion1PriceRulemax))) {
                        if(empty($specialPromotion1Countries) || in_array($this->debitor["Country_Region_Code"], $specialPromotion1Countries))
                            $productData = array(
                                "VAT_Prod_Posting_Group" => $vatProdPostingGroup,
                                "No" => $specialPromotion1Sku, 
                                "Type" => "Item", // Name
                                "Quantity" => "1", // ordered qty
                                "Unit_Price" => 0, //Price ohne Steuer
                            );
                            $verkaufsauftragArray["SalesLines"][] = $productData;
                    }
                }

                //Promotion item 2
                if(!empty($specialPromotion2Sku)) {
                    if((empty($specialPromotion2PriceRulemin) && $specialPromotion2PriceRulemin !== "0") || ($orderTotal >= $specialPromotion2PriceRulemin && $orderTotal < $specialPromotion2PriceRulemax) || ($orderTotal >= $specialPromotion2PriceRulemin &&  empty($specialPromotion2PriceRulemax))) {
                        if(empty($specialPromotion2Countries) || in_array($this->debitor["Country_Region_Code"], $specialPromotion2Countries))
                            $productData = array(
                                "VAT_Prod_Posting_Group" => $vatProdPostingGroup,
                                "No" => $specialPromotion2Sku, 
                                "Type" => "Item", // Name
                                "Quantity" => "1", // ordered qty
                                "Unit_Price" => 0, //Price ohne Steuer
                            );
                            $verkaufsauftragArray["SalesLines"][] = $productData;
                    }
                }

                //Promotion item 3
                if(!empty($specialPromotion3Sku)) {
                    if((empty($specialPromotion3PriceRulemin) && $specialPromotion3PriceRulemin !== "0") || ($orderTotal >= $specialPromotion3PriceRulemin && $orderTotal < $specialPromotion3PriceRulemax) || ($orderTotal >= $specialPromotion3PriceRulemin &&  empty($specialPromotion3PriceRulemax))) {
                        if(empty($specialPromotion3Countries) || in_array($this->debitor["Country_Region_Code"], $specialPromotion3Countries))
                            $productData = array(
                                "VAT_Prod_Posting_Group" => $vatProdPostingGroup,
                                "No" => $specialPromotion3Sku, 
                                "Type" => "Item", // Name
                                "Quantity" => "1", // ordered qty
                                "Unit_Price" => 0, //Price ohne Steuer
                            );
                            $verkaufsauftragArray["SalesLines"][] = $productData;
                    }
                }
                
                
                
            /**
             * "Der Kundenflyer" jetzt genau nur 1x der Bestellung hinzufügen.
             * Aber nur, wenn in der Backend-Konfiguration das Feld nicht leer ist.
             */
                if (!empty($kundenflyerSKU)) {
                    $productData = array(
                        "VAT_Prod_Posting_Group" => $vatProdPostingGroup,
                        "No" => $kundenflyerSKU, 
                        "Type" => "Item", // Name
                        "Quantity" => "1", // ordered qty
                        "Unit_Price" => 0, //Price ohne Steuer
                    );
                    $verkaufsauftragArray["SalesLines"][] = $productData;
                }
                     
                
                
            /**
             * add the Verpackungen and Zertifikate at the END, not directly after the Products
             * PM RH 03.12.2019, https://trello.com/c/aoymfTcT/60-phase-2-sortierung#action-5de6689f92faaa19264ede90
             */
                if (!empty($additionalArticles["verpackungen"])) {
                    foreach ($additionalArticles["verpackungen"] as $verpackungen) {
                        foreach ($verpackungen as $verpackungArtnr) {
                            $artnr = strtoupper(trim($verpackungArtnr));
                            if (empty($artnr) || $artnr==$kundenflyerSKU) { // Flyer wird separat gehandelt
                                continue;
                            }
                            $productData = array(
                                "VAT_Prod_Posting_Group" => $vatProdPostingGroup,
                                "No" => $artnr, // SKU: ist in Nav immer Uppercase. Bsp "giftcard-100" -> "GIFTCARD-100"
                                "Type" => "Item", // Name
                                "Quantity" => $item_qty, // ordered qty
                                "Unit_Price" => 0, //Price ohne Steuer
                            );
                            $verkaufsauftragArray["SalesLines"][] = $productData;
                        }
                    }
                }
                    
                if (!empty($additionalArticles["zertifikate"])) {
                    foreach ($additionalArticles["zertifikate"] as $zertifikate) {
                        foreach ($zertifikate as $zertifikatArtnr) {
                            $artnr = strtoupper(trim($zertifikatArtnr));
                            if (empty($artnr) || $artnr==$kundenflyerSKU) { // Flyer wird separat gehandelt
                                continue;
                            }

                            $productData = array(
                                "VAT_Prod_Posting_Group" => $vatProdPostingGroup,
                                "No" => $artnr, // SKU: ist in Nav immer Uppercase. Bsp "giftcard-100" -> "GIFTCARD-100"
                                "Type" => "Item", // Name
                                "Quantity" => $item_qty, // ordered qty
                                "Unit_Price" => 0, //Price ohne Steuer
                                // "Description_2"
                            );
                            $verkaufsauftragArray["SalesLines"][] = $productData;
                        }
                    }
                }

                
            /**
             * Rabatte separat als Zeile hinzufügen.
             * @link https://trello.com/c/t7O8hXRa/45-orderexport-rabattcodes-zb-f%C3%BCr-newsletter-anmeldungen
             */
                // preprint($this->orderData, __FILE__.__LINE__);
				
				
                // Die SKU des Sachkontos aus Navision, zu dem die Rabatte geschrieben werden.
                $couponCodeArtno = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/rabattsku'));
				
				/*/ @todo: Steuersatz evtl auch noch auslesen? https://trello.com/c/I3cDTuTP/88-%C3%A4nderungen-api-f%C3%BCr-versandkosten-bis-31122020
					$couponCodeVAT = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/rabattvat'));
					if (empty($couponCodeVAT)) {
						$couponCodeVAT = 19;
					}
					$couponCodeVAT = (float)"1.".$couponCodeVAT; // macht aus "19" -> "1.19"
                /**/
				
				
                // Wenn in der Bestellung ein Coupon-Code gesetzt wurde:
                // @link https://trello.com/c/t7O8hXRa/45-orderexport-rabattcodes-zb-f%C3%BCr-newsletter-anmeldungen#action-5dca9384c1638681a566f915
                    if (isset($this->orderData["Coupon Code"]) && !empty(trim($this->orderData["Coupon Code"]))) {
                        // preprint($this->orderData, __FILE__.__LINE__); die();
                        
                        /**
                         * Magento gives us the pure value. But we have to subscract the VAT (19%) here.
                         * Laut Nicky soll /1,19 gerechnet werden, 
                         * siehe https://trello.com/c/t7O8hXRa/45-orderexport-rabattcodes-zb-f%C3%BCr-newsletter-anmeldungen#comment-5dc5570b6462f90def6b2658
                         * @todo: % Discounts hier beachten
                         */
                            $baseDiscountAmount = $this->orderData["Coupon Value"]; // Wird immer als negative  brutto Euro-Summe erstellt. zB "-15.3675"
                            
							// From 01.07.2020 to 01.01.2021 because of VAT-Change in Germany: https://trello.com/c/I3cDTuTP/88-%C3%A4nderungen-api-f%C3%BCr-versandkosten-bis-31122020
								$now = date("Ymd");
								if ($now>=20210101) {
									$baseDiscountAmount = ($baseDiscountAmount*-1) / 1.19; // ohne MWST. mit 19% Satz
								} else {
									$baseDiscountAmount = ($baseDiscountAmount*-1) / 1.16; // ohne MWST. mit 16% Satz
								}
									
							
							
                            $descr2 = "Rabatt: ".$this->orderData["Coupon Value"]."€, Code: ".$this->orderData["Coupon Code"];
                            
                            
                        // Wenn keine Sachkontonr verwendet wird, wird einfach ein Zeilenrabatt dem ersten Produkt hinzugefügt.
                            if (empty($couponCodeArtno)) {
                                $msg = "Achtung: Keine Sachkonto-nr im Backend gesetzt für Rabatte! Gehe zu: Shops -> Konfig -> Pixmex/Navision -> Export. Füge deshalb den Rabatt als Line-Amount zu dem ersten Produkt hinzu";
                                $this->addLogMessage($msg, "warning");
                                
                                // Must be added as positive value, because navision substract this.
                                    // for € discounts:
                                    $verkaufsauftragArray["SalesLines"][0]["Line_Discount_Amount"] = $baseDiscountAmount;
                                    
                                    // for % discounts not needed: $verkaufsauftragArray["SalesLines"][0]["Line_Discount_Percent"] = $this->orderData["Coupon Value"];
                                    
                                    
                                // Add the information of the Discount into the line.
                                    $verkaufsauftragArray["SalesLines"][0]["Description_2"] = $descr2;
                            }
                            
                        // Hier wird die Zeile als Sachkonto hinzugefügt
                            else {

                                //If there is a different Number set for the customer country, use that code instead
                                $msg = "Sachkonto: ".$this->getEUNavDiscountSachkonto($this->debitor["Country_Region_Code"]);
                                $this->addLogMessage($msg, "debug");
                                if ($this->getEUNavDiscountSachkonto($this->debitor["Country_Region_Code"])) {
                                    $couponCodeArtno = $this->getEUNavDiscountSachkonto($this->debitor["Country_Region_Code"]);
                                }

                                $productData = array(
                                    "No" => $couponCodeArtno, 
                                    "Type" => "G_L_Account", // Name
                                    "Quantity" => "1", // ordered qty
                                    "Unit_Price" => $baseDiscountAmount*(-1), // muss mit - anfangen
                                    "Description_2" => $descr2
                                );
                                $verkaufsauftragArray["SalesLines"][] = $productData;
                            }
                    }

                
            /**
             * Amasty Gift-Cards as separate Salesorder Line
             * @link https://trello.com/c/bXjkJ32I/66-2020-02-einl%C3%B6sen-eines-gutscheins-mit-anderem-sachkonto-f%C3%BCr-navision
             */
                if (isset($this->orderData["GiftCard"]) && !empty($this->orderData["GiftCard"]["Gift Card Amount"])) {
                    
                    // Wenn keine Sachkontonr verwendet wird, wird einfach ein Zeilenrabatt dem ersten Produkt hinzugefügt.
                        if (empty($this->giftcardCodeArtno)) {
                            $msg = "Achtung: Keine Sachkonto-nr im Backend gesetzt für Gift-Cards! Gehe zu: Shops -> Konfig -> Pixmex/Navision -> Export. Füge deshalb den Rabatt als Line-Amount zu dem ersten Produkt hinzu";
                            $this->addLogMessage($msg, "warning");
                        }
                        

                    // Hier wird die Zeile dem Verkaufsauftrag (Salesorder) als neue Zeile (Line) hinzugefügt
                        else {
                            
                            /**
							 * Preisberechnung, siehe https://trello.com/c/SqZE9KHX/27-magento-gift-cards-f%C3%BCr-navision-exportieren-als-salesorderline#comment-5eb2c37a43464d1fb2c78fac
							 * Info für die buchhaltung mit NETTO Preis
							 **/
								$price = $this->orderData["GiftCard"]["Gift Card Amount"];
								$price = $price / 1.19; // ohne MWST.
								$price = $this->_exporthelper->formatPrice($price);
								$descr = "Kauf per Gutscheinkarte ({$price} € netto )"; // 

							/**
							 * Info für eCommerce-Manager (Simone)
							 **/
								$descr2 = "MageID: '{$this->orderData["GiftCard"]["Gift Card Code"]}' ({$this->orderData["GiftCard"]["Gift Card Amount"]} € brutto)"; 
                            
							/**
							 * Nach Absprache mit Simone soll das hier nicht zu einem Konto verbucht werden, sondern rein INFORMATIV als Zeile erscheinen.
							 * Deshalb verwenden wir die DESCRIPTION für die Buchhaltung mit Netto, und Descr2 für Simone zum Nachvollziehen durch Magento.
							 * @link https://trello.com/c/SqZE9KHX/27-magento-gift-cards-f%C3%BCr-navision-exportieren-als-salesorderline#comment-5eb40d72c397c32051b7e9c5
							 **/
								$productData = array(
									// "No" => "", // $this->giftcardCodeArtno, 
									// "Type" => "", // G_L_Account
									// "Quantity" => "1", // ordered qty
									// "Unit_Price" => $price, // Normal siehe https://trello.com/c/SqZE9KHX/27-magento-gift-cards-f%C3%BCr-navision-exportieren-als-salesorderline#comment-5eb2c37a43464d1fb2c78fac
									// "Line_Amount" => $price*(-1), // muss mit - anfangen, siehe https://trello.com/c/SqZE9KHX/27-#comment-5eb39d5219d21353d1a27094
									// "Line_Discount_Percent" => "", // Evtl ausfüllen? siehe https://trello.com/c/SqZE9KHX/27-#comment-5eb39d5219d21353d1a27094
									"Description" => mb_substr($descr, 0, 50), // laut Navision darf das Feld nur 50 Zeichen haben
									"Description_2" => mb_substr($descr2, 0, 50), // laut Navision darf das Feld nur 50 Zeichen haben
								);
								
                            $verkaufsauftragArray["SalesLines"][] = $productData;
                        }
                }
                    
            
            // preprint($verkaufsauftragArray, "verkaufsauftragArray, ".__FILE__.__LINE__);
            // preprint($this->order->getBaseDiscountAmount(), "getBaseDiscountAmount");
            // preprint($zertifikate, "Zertifikate, ".__FILE__.__LINE__); preprint($additionalArticles, "additionalArticles, ".__FILE__.__LINE__); 
            // preprint($this->orderData, __FILE__.__LINE__);  preprint($verkaufsauftragArray, __FILE__.__LINE__); die();
            $verkaufsauftragArray["VAT_Prod_Posting_Group"] = $vatProdPostingGroup;
            return $verkaufsauftragArray;
        }
        
    
        
    /**
     * Add Billing- and Shippingadresses to the Salesorder
     * Also consider different Billing- / Shipping-Adresses
     * 
     * Packstation: Auf der Rechnung von Navision soll stehen: "Name \n Postnummer \n Packstation-Nr \n PLZ Ort".
     * 
     *  Magento speichert die Nr der Packstation in "Straße" ab (zB "Packstation 131")
     *  Die Postnr steht dann in "Company" (zB 89080767x)
     *  Versandmethode ist manchmal "DHL Versand", hieraus kann man also nichts ableiten.
     * 
     * @link https://trello.com/c/3v1C19fW/51-lieferung-an-packstation#comment-5dcc05a1aec3a67060a50b4e
     */
        public function addAdressesToVerkaufsauftrag($verkaufsauftragArray) {
            
            $fieldsNavMage = array();
            // Der Part Oben im Verkaufsauftrag muss die rechnungsadresse sein.https://trello.com/c/7yfEDXmg/17-09-order-export-magento-navision-verkaufsauftrag#action-5de68954de01386c51e36700
                $fieldsNavMage["Sell_to_Customer_Name"] = "Customer Name Full";
                $fieldsNavMage["Sell_to_Customer_Name_2"] = "Customer Company";
                $fieldsNavMage["Sell_to_Address"] = "Billing Address";
                $fieldsNavMage["Sell_to_Post_Code"] = "Billing PostCode";
                $fieldsNavMage["Sell_to_City"] = "Billing City";
                
            // Dieser Part steht unter "Fakturierung", siehe https://trello.com/c/7yfEDXmg/17-09-order-export-magento-navision-verkaufsauftrag#action-5de68954de01386c51e36700
                $fieldsNavMage["Bill_to_Name"] = "Customer Name Full";
                $fieldsNavMage["Bill_to_Name_2"] = "Customer Company";
                $fieldsNavMage["Bill_to_Address"] = "Billing Address";
                $fieldsNavMage["Bill_to_Post_Code"] = "Billing PostCode";
                $fieldsNavMage["Bill_to_City"] = "Billing City";
                // $fieldsNavMage["Shipping Method"] = "Shipment_Method_Code"; // DHL - Versand -> wird zu?

                
            /*
             * updated by AA on 5.12.2019
             * https://trello.com/c/p2JjVqhG/114-gutscheine-%C3%BCber-amasty-funktionen-vouchers-from-amasty-extension-gift-card-giftcard#comment-5ddcda04a321044b60094571
             * For gift card there is no shipping address, so skip shipping address
             */
                if (isset($this->orderData["Shipping Address"])){

                    // Felder unter "Lieferung", siehe https://trello.com/c/7yfEDXmg/17-09-order-export-magento-navision-verkaufsauftrag#action-5de68954de01386c51e36700
                    $fieldsNavMage["Ship_to_Name"] = "Shipping Name";
                    $fieldsNavMage["Ship_to_Address_2"] = "Shipping Company";
                    $fieldsNavMage["Ship_to_Address"] = "Shipping Address";
                    $fieldsNavMage["Ship_to_Post_Code"] = "Shipping Postcode";
                    $fieldsNavMage["Ship_to_City"] = "Shipping City";
                }


            foreach ($fieldsNavMage as $navField => $mageField) {
                if (!isset($this->orderData[$mageField])) {
                    $message = "Das Feld {$mageField} ist in this->orderData nicht vorhanden. Siehe ".__FILE__.__LINE__; 
                    $this->addLogMessage($message, "debug");
                    continue;
                }
                
                $value = trim($this->orderData[$mageField]);
                if ($value=="") {
                    continue;
                }
                
                $verkaufsauftragArray[$navField] = $value;
            }
            

            // preprint($verkaufsauftragArray, __FILE__.__LINE__); preprint($this->orderData, __FILE__.__LINE__); die();
            return $verkaufsauftragArray;
        }
        
        
        
        
        
    /**
     * Depending on the Mage-Order State we have to put differnt informations in the field "External_Document_No"
     *  Pending: "pending #####"
     *  Processing: "Webshop Bestellung #####"
     * @link https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing
     */
        public function addStatusToVerkaufsauftrag($verkaufsauftragArray) {
            
            // preprint($this->orderData, __FILE__.__LINE__);
            if (!isset($this->orderData)) {
                throw new \Exception("this->orderData was not found in ".__FILE__.__LINE__.", so we could not handle the Order-Status");
                return false;
            }
            
            if (!isset($this->orderData["Order Status"])) {
                throw new \Exception("Field 'Order Status' not found in ".__FILE__.__LINE__.", so we could not handle the Assignment to Fields for the Nav-Salesorder");
                return false;
            }
            
            if (!isset($this->orderData["Order Increment Id"])) {
                throw new \Exception("Field 'Order Increment Id' not found in ".__FILE__.__LINE__.", so we could not handle the Assignment.");
                return false;
            }
            
            
            // Dieses Feld war schon immer ungenutzt.
                $verkaufsauftragArray["Prepmt_Posting_Description"] = $this->orderData["Order Status"]; // ." seit ".date("d.m.Y H:i:s"); Must not add additional test, because otherwise Agolutinos Code won't work. See https://trello.com/c/CALZB9Ax/62-phase-3-buchen-drucken-projektleitung-f%C3%BCr-agolution-navision-agentur-m%C3%BCnster
            
            
            //Wir sollen hier nur noch die Order ID übergeben, siehe https://projects.zoho.com/portal/pixelmechanics2#taskdetail/1781812000000381050/1781812000000400001/1781812000000992003
            $verkaufsauftragArray["External_Document_No"] = $this->orderData["Order Increment Id"]; // Magento-Ordernr, siehe https://trello.com/c/7yfEDXmg/17-api-09-order-export-magento-navision-verkaufsauftrag-navision-teil-2#action-5dc54b18a8702e7cc1dc346c
            
            /** Nicht mehr benötigt, siehe https://projects.zoho.com/portal/pixelmechanics2#taskdetail/1781812000000381050/1781812000000400001/1781812000000992003
            if (isset($this->orderData["Order Status"]) && $this->orderData["Order Status"]!=="processing") {
                $verkaufsauftragArray["External_Document_No"] = $this->orderData["Order Increment Id"]; // Magento-Ordernr, siehe https://trello.com/c/7yfEDXmg/17-api-09-order-export-magento-navision-verkaufsauftrag-navision-teil-2#action-5dc54b18a8702e7cc1dc346c
            }
            */
            
            // preprint($verkaufsauftragArray, __FILE__.__LINE__); preprint($this->orderData, __FILE__.__LINE__); die();
            return $verkaufsauftragArray;
        }            
        
        /**
         * Get the NAVISION Vat identifier based on the Magento country short tag
         *
         * @param string $countryShortTag
         * @return string NAV identifier
         * PM LB 08/2021
         */
        private function getVatInformation($countryShortTag) {
            $vatatrray = array();
            
            $vatarray["DE"] = "VAT19";
            $vatarray["GB"] = "VAT0";
            $vatarray["AT"] = "VAT20 OSS";
            $vatarray["BE"] = "VAT21 OSS";
            $vatarray["BG"] = "VAT20 OSS";
            $vatarray["CZ"] = "VAT21 OSS";
            $vatarray["DK"] = "VAT25 OSS";
            $vatarray["EE"] = "VAT20 OSS";
            $vatarray["ES"] = "VAT21 OSS";
            $vatarray["FI"] = "VAT24 OSS";
            $vatarray["FR"] = "VAT20 OSS";
            $vatarray["HR"] = "VAT25 OSS";
            $vatarray["HU"] = "VAT27 OSS";
            $vatarray["IE"] = "VAT23 OSS";
            $vatarray["IT"] = "VAT22 OSS";
            $vatarray["LT"] = "VAT21 OSS";
            $vatarray["LU"] = "VAT17 OSS";
            $vatarray["LV"] = "VAT21 OSS";
            $vatarray["MT"] = "VAT18 OSS";
            $vatarray["NL"] = "VAT21 OSS";
            $vatarray["PL"] = "VAT23 OSS";
            $vatarray["PT"] = "VAT23 OSS";
            $vatarray["RO"] = "VAT19 OSS";
            $vatarray["SE"] = "VAT25 OSS";
            $vatarray["SI"] = "VAT22 OSS";
            $vatarray["SK"] = "VAT20 OSS";

            return $vatarray[$countryShortTag];
        }


        /**
         * Get the correct Navision Account number for discount codes by the country short tag
         *
         * @param string $countryShortTag
         * @return string Account number in NAV
         * 
         * PM LB 12/21
         */
        private function getEUNavDiscountSachkonto($countryShortTag) {
            $accountArray = array();
            
            $accountArray["DE"] = "8790";
            $accountArray["AT"] = "8791";
            $accountArray["BE"] = "8792";
            $accountArray["BG"] = "8793";
            $accountArray["CZ"] = "8794";
            $accountArray["DK"] = "8795";
            $accountArray["EE"] = "8796";
            $accountArray["ES"] = "8797";
            $accountArray["FI"] = "8798";
            $accountArray["FR"] = "8799";
            $accountArray["HR"] = "8789";
            $accountArray["HU"] = "8788";
            $accountArray["IE"] = "8787";
            $accountArray["IT"] = "8786";
            $accountArray["LT"] = "8785";
            $accountArray["LU"] = "8784";
            $accountArray["LV"] = "8783";
            $accountArray["MT"] = "8782";
            $accountArray["NL"] = "8781";
            $accountArray["PL"] = "8780";
            $accountArray["PT"] = "8779";
            $accountArray["RO"] = "8778";
            $accountArray["SE"] = "8777";
            $accountArray["SI"] = "8776";
            $accountArray["SK"] = "8775";

            if(!isset($accountArray[$countryShortTag])) {
                return false;
            }

            return $accountArray[$countryShortTag];

        }

        
        
        
} // end of class