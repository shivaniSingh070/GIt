<?php

/**
 * @author : AA
 * @template-Version : Magento 2.3.1
 * @description : ExportOrder Export Model to generate the XML and the PDF file
 * @date : 19.06.2019
 * @Trello: https://trello.com/c/7yfEDXmg
 */

namespace Pixelmechanics\ExportOrder\Model\Order\Creditmemo;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Pixelmechanics\ExportOrder\Helper\Data;
use Pixelmechanics\ExportOrder\Logger\Logger;
use Psr\Log\LoggerInterface;

class Export extends \Magento\Framework\Model\AbstractModel {

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Data
     */
    protected $_exporthelper;

    /**
     * @var OrderInterface
     */
    protected $_orderInterface;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var Logger
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
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    protected $_directoryList;
    /**
     * @var CreditmemoInterface
     */
    private $creditMemo;
    /**
     * @var CreditmemoRepository
     */
    private $_creditMemoRepository;
    /**
     * @var string
     */
    private $creditMemoId;
    /**
     * @var string|null
     */
    private $creditMemo_increment_id;
    /**
     * @var array
     */
    private $creditMemoData;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $_exporthelper
     * @param OrderInterface $orderInterface
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CollectionFactory $productCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param Logger $logger
     * @param LoggerInterface $psrlogger
     * @param ManagerInterface $messageManager
     * @param TransactionSearchResultInterfaceFactory $transXInterface
     * @param \Magento\Catalog\Helper\Data $taxHelper
     * @param \Magento\Framework\App\Filesystem\DirectoryList $_directoryList
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        Data $_exporthelper,
        OrderInterface $orderInterface,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CollectionFactory $productCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Logger $logger,
        LoggerInterface $psrlogger, // Siehe https://magento.stackexchange.com/questions/119992/exception-handling-in-magento-2
        ManagerInterface $messageManager, // PM RH 17.10.2019: https://www.rakeshjesadiya.com/display-success-and-error-messages-using-magento-2/
        TransactionSearchResultInterfaceFactory $transXInterface, // PM RH 18.10.2019 to retrieve the Transaction ID
        \Magento\Catalog\Helper\Data $taxHelper, // Get the itemprice WITH Tax seems to be more complicated. See https://gielberkers.com/get-product-price-including-excluding-tax-magento-2/
        \Magento\Framework\App\Filesystem\DirectoryList $_directoryList,
        CreditmemoRepository  $_creditMemoRepository
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
        $this->_directoryList = $_directoryList;
        $this->_creditMemoRepository = $_creditMemoRepository;
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



        include_once($this->_directoryList->getRoot()."/pm_navision_helper.php");
        $result = $this->getNavisionCredentials();
        if ($result===false) {
            return false;
        }

    }

    /**
     * @link https://magento.stackexchange.com/questions/169494/magento-2-load-order-by-id-in-customer-account-order-view
     * @param $id
     * @return Magento\Sales\Model\Order\Interceptor
     */
    public function getOrderByID($order_id) {
        
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $this->order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);

        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$orderRep = $objectManager->create(\Magento\Sales\Model\OrderRepository::class);

        $this->order = $this->_orderInterface->load($order_id);
        //$this->order = $orderRep->get($order_id);
        //preprint($this->order->getExtensionAttributes(), __FILE__.__LINE__, true); die();
        
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
     * @link https://magento.stackexchange.com/questions/169494/magento-2-load-order-by-id-in-customer-account-order-view
     * @param $creditMemoId
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemoByID($creditMemoId)
    {
        try {
            $this->creditMemo = $this->_creditMemoRepository->get($creditMemoId);
        } catch (InputException $e) {
        } catch (NoSuchEntityException $e) {
            $msg = __FILE__.__LINE__.": ".get_class($this->order)."() ->  No Credit memo with ".$creditMemoId. " Found";//.print_r($this->order->toArray(), 1);
            $this->creditMemoLog($creditMemoId, $msg);
        }
        //preprint($this->order->getExtensionAttributes(), __FILE__.__LINE__, true); die();

        $msg = __FILE__.__LINE__.": ".get_class($this->order)."() -> ";//.print_r($this->order->toArray(), 1);
        $this->creditMemoLog($creditMemoId, $msg);

        $items = $this->creditMemo->getItems();
        if (empty($items)) {
            $message = "WARNING: Order-Object ".get_class($this->order)." does not contain Items!";
            if (ENVIRONMENT != "production") {
                $message .= "\n Perhaps the Order was not fully loaded? See ".__FILE__.__LINE__;
            }
            $this->addLogMessage($message, "debug");

            // preprint($message, __FILE__.__LINE__);
            // preprint($this->order->toArray(), __FILE__.__LINE__);
            // preprint(get_class_methods($this->order), get_class($this->order).", ".__FILE__.__LINE__); die();
        } else {
            $message = "Order-Object ".get_class($this->order)." HAS  Items :-) ".__FILE__.__LINE__;
        }
        $this->creditMemoLog($creditMemoId, $message);

    }

    /**
     * Store debugging text into a text-file in var/log related to the ID of an order.
     * @param $order_id
     * @param string $msg
     */
    public function orderLog($order_id, $msg) {
        $orderLogFilename = $this->_directoryList->getRoot()."/var/log/orderexport_".$order_id.".txt";
        $msg = date("d.m.Y H:i:s")." :: ".$msg."\n-----------\n\n";
        file_put_contents($orderLogFilename, $msg, FILE_APPEND);
    }

    /**
     * Store debugging text into a text-file in var/log related to the ID of an order.
     * @param $creditMemoId
     * @param string $msg
     */
    public function creditMemoLog($creditMemoId, $msg) {
        $orderLogFilename = $this->_directoryList->getRoot()."/var/log/creditmemoxport_".$creditMemoId.".txt";
        $msg = date("d.m.Y H:i:s")." :: ".$msg."\n-----------\n\n";
        file_put_contents($orderLogFilename, $msg, FILE_APPEND);
    }


    /**
     * reads the Sales-Order ID/Key from a text-file as temporary solution
     * @param $order_id
     * @param string $msg
     * TODO @lorenz please check this
     */
    public function getNavisionDataForOrderID($order_id) {

        if (!isset($this->order)) {
            $this->getOrderByID($order_id);
        }

        $return = array();
        include_once($this->_directoryList->getRoot()."/pm_navision_helper.php");



        // check, if we can get the infos from a file (old way until 18.11.2019)
        $orderLogFilename = $this->_directoryList->getRoot()."/orderexport/order_".$order_id.".dat";
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
     * @param $order_id
     * @param string $msg
     * @link: MageSaveOrder https://magento.stackexchange.com/questions/163916/magento-2-how-to-add-custom-data-in-order-email
    https://magento.stackexchange.com/questions/180371/magento-2-save-additional-data-to-order
    https://www.yereone.com/blog/magento-2-how-to-add-new-order-attribute/
    https://community.magento.com/t5/Magento-2-x-Programming/How-to-add-custom-field-in-order-sales-table-and-used-it/td-p/103625
     * TODO @lorenz please check this
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

        $folder = $this->_directoryList->getRoot()."/orderexport/";
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
     * Main function to export creditmemo.
     *
     * @param CreditmemoInterface $creditMemo (Magento Credit memo entity ID) || CreditMemo object
     * @param bool $order
     * @return bool|mixed|void
     */
    public function exportOrder($creditMemo, $order=false) {
        $this->orderData = null;
        if(is_string($creditMemo)) {
            $this->creditMemoId = $creditMemo;
            $this->getCreditmemoByID($creditMemo);
            $creditMemoId = $creditMemo;
        } else {
            $this->creditMemo = $creditMemo;
            $creditMemoId = $creditMemo->getId();
        }

        //initialize order
        $this->getOrderByID($this->creditMemo->getOrderId());

        if (!method_exists($this->creditMemo, "getId")) {
            $this->_logger->error('Creditmemo. # '. $creditMemoId. 'exists, but could not be loaded from Magento.');
            return;
        }

        // Get the order's increment id
        $this->creditMemo_increment_id = $this->creditMemo->getIncrementId();
        // preprint($this->order_increment_id, __FILE___.__LINE__); die();

        // Convert the Order-Datails from Magento to exportable values.
        if($this->generateCreditMemoDataArray($creditMemoId, $this->creditMemo_increment_id)){
           $bolllean =true;
        }else{
            $bolllean= false;
        }
        
        /**
         * Generate the XML file in the path of exported orders.
         * Can be switched on/off by a backend-setting: /pmadmin/admin/system_config/edit/section/pixelmechanics_configuration/
         * @See \app\code\Pixelmechanics\ExportOrder\etc\adminhtml\system.xml
         */
        $doCreateXML = (int)($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/createXML')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($doCreateXML) {
            $result = $this->createXmlFile($this->creditMemoData);
            if ($result===false) {
                return false;
            }
        }

        
        /**
         * Add the data to Navision by SOAP-Requests
         * @link [Orderdetail Comments incl Styling](https://trello-attachments.s3.amazonaws.com/5d39aa9c39cbe152bdb91be5/5c86df8ed8b5b55dc3e416a1/eaf970cf1e1f3cc4a417522d27ed078b/image.png)
         * TODO To check this scope now PM AY
         */
        $send2navision = (int)($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/send2navision')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($send2navision) {
           // return $this->add2navision();
        } else {
//            $this->addLogMessage("Not sending to Navision, because it is disabled. See Shops -> Konfiguration -> Pixelmechancis/Navision", "debug");
        }

        return $bolllean;
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
       // echo '<pre>'; print_r($this->orderData);
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
     * @param $creditMemoId
     * @param $creditmemo_increment_id
     */
    public function generateCreditMemoDataArray($creditMemoId, $creditmemo_increment_id) {
        // Get customer data from order
        $customer_id = $this->creditMemo->getOrder()->getCustomerId();
        $isGuest = $this->creditMemo->getOrder()->getCustomerIsGuest();

        // Create a date object of the Magento order date to format it later
        $creditMemo_date = date_create($this->creditMemo->getCreatedAt());

        // Subtotal (Total without tax and shipping costs), rounded to 2 deciamals
        $creditMemo_subtotal = $this->creditMemo->getSubtotal() + $this->creditMemo->getDiscountAmount();
        $rounded_subtotal = $this->_exporthelper->formatPrice($creditMemo_subtotal);

        // Order store info
        $creditMemo_store = $this->creditMemo->getOrder()->getStoreName();

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

        $order_id = $this->creditMemo->getOrder()->getId();
        $return =$this->getNavisionDataForOrderID($order_id);
        
        /**
         * Save data of order and customer in array $this->orderData. This is used for the creation of the XML file later.
         */
        
        $this->creditMemoData = array(
            "Host" => $host,
            "IP" => $ip,
            "Pixmex-Environment" => ENVIRONMENT,
            "Order Type" => 'Online', // Order type (always 'Online')
            "Order Status" => $this->creditMemo->getOrder()->getStatus(),  // processing? Complete? Pending?
            "Creditmemo Store" => $creditMemo_store, // Storeview
            "Creditmemo Date" => date_format($creditMemo_date, 'd.m.y'), // Date the order was placed
            "Creditmemo Increment Id" => $creditmemo_increment_id, // Order increment id

            "Subtotal" => $rounded_subtotal, // Subtotal (Total without tax,discounts and shipping)
            "Grand Total" => $this->creditMemo->getGrandTotal(), // Grand total
            "Discount Amount" => $this->creditMemo->getDiscountAmount(), // Gift-Card Data? See https://trello.com/c/bXjkJ32I/66-2020-02-einl%C3%B6sen-eines-gutscheins-mit-anderem-sachkonto-f%C3%BCr-navision#comment-5eb02ffd8ebf8c826ce9804f and https://bitbucket.org/pixelmechanics/engelsrufer-relaunch/pull-requests/626/feature-20200218-giftcard-export-rh/diff#comment-148221589

            "Customer isGuest" => $isGuest, // 0 or 1, if order placed as GUEST User
            "Customer Id" => $customer_id, // Magento customer id
            "Customer Email" => $this->creditMemo->getOrder()->getCustomerEmail(),  
            "Sell_to_Customer_No" => $return["debitor_no"]
        );

        
        /**
         * Kundennummer kommt aus Navision. Der Einfachheit halber generieren wir Sie mit "BC1000000" und addieren die Magento ID dazu, dann sollte hier kein Problem entstehen.
         * PM RH 25.11.2019: Da wir die Debitor-ID direkt bei der Order abspeichern und nicht mehr beim Magento-Kunden, ist diese Meldung nicht mehr relevant.
         * @todo: Lieber die Order prüfen, ob hier erp_id oder salesorder_id vorhanden sind.
         */
        if ($customer_id>0) {

            $customer = $this->_customerRepositoryInterface->getById($customer_id);
            $erp_id = $this->customerGetERPID($customer_id);

            $this->creditMemoData["customer_erp_id"] = $erp_id;

        } else {
            // "Kein Customer mit der ID '$customer_id' gefunden. $erp_id";
            // $msg = "Gast-Bestellung, kann Debitor-ID nicht mit Magento-Customer verknüpfen.";
            // $this->addLogMessage($msg, "debug");
        }


        if (isset($this->creditMemoData["erp_id"])) {
            $debitor["E_Mail"] = $this->creditMemoData["Customer Email"];
            $this->creditMemoData["Sell_to_Customer_No"] = $this->creditMemoData["erp_id"];
        }
        $this->creditMemoData["Posting_Description"] = "Retoure B2C";
        $this->creditMemoData["Credit_Memo_Type"] = "Credit Memo";
        $this->creditMemoData["External_Document_No"] = "Teilgutschrift zu " ;
       
        $creditMemo = $this->creditMemo;
        $netPrice= $this->creditMemoData["Grand Total"]/1.19;

         /**
         * Save data of order in array $this->creditMemoData.
         * @link https://trello.com/c/T1bCYqVB/31-api-09-order-export-magento-navision-debitor-navision-teil-2
         */
        /**
         * add VAT_Prod_Posting_Group index to CreditMemo SalesLine
         * created by ha
         * trello Related to this card https://trello.com/c/5dHZOWuA/208-schnitt-stellen-erweiterung-retouren-info-auch-in-altem-trello-ticket
         * 
         */
        $shippingAddressData = $this->creditMemo->getOrder()->getShippingAddress()->getData();
        $countryRegionCode = $shippingAddressData["country_id"];
        $discountSachKonto = $this->getEUNavDiscountSachkonto($countryRegionCode);
        $vatPostingGroup   = $this->getVatInformation($countryRegionCode);
        foreach ($creditMemo->getItemsCollection() as $item) {
            if ($item->getPrice() != 0){
                $netPrice = $netPrice - ($item->getPrice()*$item->getQty());
            $Data = array(
                "VAT_Prod_Posting_Group" => $vatPostingGroup,
                "No" => $item->getSku(),
                "Type" => "Item", // Name
                "Quantity" => $item->getQty(), // ordered qty
                "Unit_Price" => $item->getPrice()
            );}
            if ($item->getPrice() != 0){
            $this->creditMemoData["SalesLines"][] = $Data;
            }
            
        } 

       
        $Data = array(
            "No" => $discountSachKonto,
            "Type" => "G_L_Account", // Name
            "Quantity" => 1, // ordered qty
            "Unit_Price" =>$netPrice
        );

        $this->creditMemoData["SalesLines"][] =$Data ; 
        $creditMemoDataArray = $this->creditMemoData;
       
        $msg = __FILE__.__LINE__.", ".print_r($this->creditMemoData, 1);
        $this->creditMemoLog($creditMemoId, $msg);

        // PM RH: use this line, to see the generated Data before it is being sent to navision
        //preprint($this->orderData, "orderData, ".__FILE__.__LINE__); preprint($this->order->getBaseSubtotalWithDiscount(), "getBaseSubtotalWithDiscount, ".__FILE__.__LINE__); die();
        //print_r($return["debitor_no"]); das ist debitor no aus der bestellung  
        $webServiceName = "Verkaufsgutschrift"; 
        $this->navision_url = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navision/webservice_url'));
        $this->navision_login = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navision/webservice_login'));
        $this->navision_pwd = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navision/webservice_pw')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->navision_soapAction = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navision/webservice_soapaction')); // , \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->navHelper = new \Nav($this->navision_url, $webServiceName, $this->navision_login, $this->navision_pwd, $this->navision_soapAction);
        if($return["debitor_no"] ==""){
            $this->addLogMessage("die Bestellung muss zu erst zu Nav exportiert werden, dann darf man erst diesen Gutschriftt mit der ID ".$creditmemo_increment_id." exportieren", "error");
            $bolllean = false;
            }else {
        $this->navHelper->createCreditMemos($customer_id, $creditMemoDataArray);
        $bolllean = true;
        }
        return $bolllean;
    }


    /**
     * Gets the ERP-ID from navision into the custom customer-attribute
     * @link https://trello.com/c/ytbOL9Aw/291-prio-1-new-customer-numbers-erp-id#comment-5dc3eb2c1005d233c1b46635
     * @param $customer_id
     * @param $debitorNo
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
     * @param $customer_id
     * @param $debitorNo
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
     * @param $message String the message.
     * @param $this->order Magento ORDER-Object
     * @param $type error, notice, success, warning
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
     * @param array $xml_basic_data
     **/
    protected function createXmlFile($xml_basic_data) {
        //print_r($xml_basic_data); die('XML');
        $currentDate = date("Ymd");
        // Get the directory path of exported orders. Create the directory if not exists with writing permissions.
        $exportDirectory = $this->_exporthelper->getOrderExportDirectoryPath();
        if (!file_exists($exportDirectory)) {
            mkdir($exportDirectory, 0644, true); // should be 0644 and not 0777.
        }
        $filename = $this->_exporthelper->getFilenameOfOrderExport($exportDirectory, $currentDate, $this->creditMemo_increment_id);

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
        // Get all credit items
        $creditMemo_items = $this->creditMemo->getItems();
        $creditMemo_item_count = 0;

        // Loop through ordered items
        foreach ($creditMemo_items as $item) {
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
            $this->addProductRowXML($xml_item_data, $creditMemo_item_count, $Record);
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
            $message = "Fehler im Order-Export: XML-Datei {$filename} für Order-ID: ".$this->creditMemo->getIncrementId()." nicht erstellt.";
            $this->addLogMessage($message, "notice") ;
            return false;
        }

        // Log order export sucess.
        $customer_info = "Customer: " . @$xml_basic_data['Customer Name Full'];
        $sucessmsg = "Order #".$this->creditMemo-> getIncrementId()." was successfully exported from Magento. ". $customer_info;
        $this->_logger->info($sucessmsg); // Logmessage




        // @see convertByte() in /pm_helper.php
//        $message = "Order-Export: XML-Datei erstellt: {$filename} (".convertByte(filesize($filename)).")";
//        $this->addLogMessage($message, "notice") ;

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
        if(isset($this->order_increment_id)){
        $message = "Export DEBITOR from Order {$this->order_increment_id} to Navision {$this->navision_url} SUCCESS: ".$result["No"]."\n\n";
        $this->addLogMessage($message, "success");
        }
        elseif(isset($this->creditmemo_increment_id)){
            $message = "Export DEBITOR from Order {$this->creditmemo_increment_id} to Navision {$this->navision_url} SUCCESS: ".$result["No"]."\n\n";
            $this->addLogMessage($message, "success");
            }
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
     * @param $mageOrder magento-Order Object.
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
          /**
        $customer_id = $this->orderData["Customer Id"]; // $mageOrder["Customer ID"]
        if (empty($customer_id)) {
            // preprint($mageOrder, $debitorID.__FILE__.__LINE__); die();
            return $debitorID;
        }
        */
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
     * @param $navAuftragnr, example "AT143785"
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
         * Die SKU des Sachkontos aus Navision, zu dem die Rabatte geschrieben werden
         * @link https://trello.com/c/SqZE9KHX/27-magento-gift-cards-f%C3%BCr-navision-exportieren-als-salesorderline#comment-5dc180e6ab4bc564203b7f7f
         **/
        $this->giftcardCodeArtno = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/giftcardsku'));
        if (empty($this->giftcardCodeArtno)) {
            $this->giftcardCodeArtno = "1718"; // Default
        }



        $auftrag = array();
        $verkaufsauftragArray = array();

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
            $priceInclTax = $this->_exporthelper->formatPrice($item->getRowTotalInclTax());
            // $priceInclTax = $this->_exporthelper->formatPrice($item->getPriceInclTax());

            /**
             * Here we have to set Keys, that Naavision can use.
             */
            $productData = array(
                "No" => $artnrNAV, // SKU: ist in Nav immer Uppercase. Bsp "giftcard-100" -> "GIFTCARD-100"
                "Type" => "Item", // Name
                "Quantity" => $item_qty, // ordered qty
                "Unit_Price" => $item_price, //Price ohne Steuer
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
         * "Der Kundenflyer" jetzt genau nur 1x der Bestellung hinzufügen.
         * Aber nur, wenn in der Backend-Konfiguration das Feld nicht leer ist.
         */
        if (!empty($kundenflyerSKU)) {
            $productData = array(
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


        $verkaufsauftragArray["External_Document_No"] = "Webshop Bestellung ".$this->orderData["Order Increment Id"]; // Magento-Ordernr, siehe https://trello.com/c/7yfEDXmg/17-api-09-order-export-magento-navision-verkaufsauftrag-navision-teil-2#action-5dc54b18a8702e7cc1dc346c
        if (isset($this->orderData["Order Status"]) && $this->orderData["Order Status"]!=="processing") {
            $verkaufsauftragArray["External_Document_No"] = $this->orderData["Order Status"]." #".$this->orderData["Order Increment Id"]; // Magento-Ordernr, siehe https://trello.com/c/7yfEDXmg/17-api-09-order-export-magento-navision-verkaufsauftrag-navision-teil-2#action-5dc54b18a8702e7cc1dc346c
        }

        // preprint($verkaufsauftragArray, __FILE__.__LINE__); preprint($this->orderData, __FILE__.__LINE__); die();
        return $verkaufsauftragArray;
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
        /**
         * Get the correct Vat Information codes by the country short tag
         *
         * @param string $countryShortTag
         * @return string $vatarray
         * 
         * PM ha 05/22
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


} // end of class
