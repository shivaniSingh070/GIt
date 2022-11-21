<?php

/**
 * @author : AA
 * @description : ExportOrder Trigger Order Export After Success.
 * @date : 24.06.2019
 * @Trello: https://trello.com/c/7yfEDXmg
 */

namespace Pixelmechanics\ExportOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

class TriggerOrderExport implements ObserverInterface {

    /**
     * @var \Pixelmechanics\Venedor\Helper\Data
     */
    protected $_pmhelper;

    /**
     * @var \Pixelmechanics\ExportOrder\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Pixelmechanics\ExportOrder\Model\Order\Export
     */
    protected $_orderExport;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * 
     * @param \Pixelmechanics\ExportOrder\Model\Order\Export $orderExport
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Pixelmechanics\ExportOrder\Logger\Logger $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
    \Pixelmechanics\ExportOrder\Model\Order\Export $orderExport,
    \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
    \Pixelmechanics\ExportOrder\Logger\Logger $logger,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_orderExport = $orderExport;
        $this->_orderRepository = $orderRepository;
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
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
     * This action will be fired when a customer comes to the success action of checkout controller.
     * */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        
        // Backendsetting: Shops -> Konfig -> Pixelmechancis -> Navision Export 
        $doExportProcessingOnly = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/doExportProcessingOnly'));
        
        // The Magento event "checkout_onepage_controller_success_action" passes the variable order_ids as parameter. It only returns one id.
        $orderids = $observer->getEvent()->getOrderIds();
        foreach ($orderids as $order_id) {
            // If the checkout_onepage_controller_success_action doesn't return the order_id, return instead and added log in file var/log/orderexport.log   
            if (!$order_id) {
                $this->_logger->error('Bestellnr. konnte nicht aus dem observierten Event (Bestellbestätigung) ausgelesen werden.');
                return;
            }

            // Load the order
            $order = $this->_orderRepository->get($order_id);
            
            $msg = "#$order_id : ".__FILE__.__LINE__;
            $this->orderLog($order_id, $msg);
            
            
            /**
             * Trigger the order export only when Processing? Or everytime?
             * PM RH 15.11.2019: Export the order always, and on update, update the sales-order in navision.
             **/
                $orderState = $order->getState();
                    $msg = "Order-State is: $orderState, doExportProcessingOnly: $doExportProcessingOnly. See ".__FILE__.__LINE__;
                    $this->_logger->debug($msg); // @link https://inchoo.net/magento-2/magento-2-logging/
                    $this->orderLog($order_id, $msg);

                // Processing is handelt by the update-method.
                    // Paypal ist allerdings direkt schon auf processing.. deshalb mal so testen // if ($orderState!=="processing") {
                        $this->_orderExport->exportOrder($order_id);                    
                    // }
                
                /* if ($doExportProcessingOnly && $orderState == "processing") {
                }
                if (!$doExportProcessingOnly) {
                    $this->_orderExport->exportOrder($order_id);
                }
                /**/
        }
        
        // preprint(__FILE__, __LINE__); die();
    }

}
