<?php

/**
 * @author : AA
 * @description : ExportOrder Trigger Order Export After Success.
 * @date : 12.11.2019
 * @Trello: https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing#comment-5dca84462a7f161585cbfb79
 */

namespace Pixelmechanics\ExportOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

class TriggerOrderExportOnOrderChange implements ObserverInterface {

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * 
     * @param \Pixelmechanics\ExportOrder\Model\Order\Export $orderExport
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Pixelmechanics\ExportOrder\Logger\Logger $logger
     */
    public function __construct(
        \Pixelmechanics\ExportOrder\Model\Order\Export $orderExport,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Pixelmechanics\ExportOrder\Logger\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
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
     * This action will be fired after order saved (sales_order_save_after)
     * */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        
        //get value from system -> configuration -> Pixelmechanics -> Navision Configuration -> Export only when State changed to processing?
            $doExportProcessingOnly = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/doExportProcessingOnly'));
            
            
        // The Magento event "sales_order_save_after" passes the order object.
            // @var Magento\Framework\Event
            $event = $observer->getEvent();
            
            $order = $event->getOrder();
            $order_id = $order->getId();  
            
        // If the sales_order_save_after doesn't return the order_id, return instead and added log in file var/log/orderexport.log   
            if (!$order_id) {
                $this->_logger->error('Bestellnr. konnte nicht aus dem observierten Event (Bestellbestätigung) ausgelesen werden.');
                return;
            }
            
            
            $msg = "Executing TriggerOrderExportOnOrderChange() for Order #$order_id in ".__FILE__.__LINE__;
            $this->orderLog($order_id, $msg);

            
            
            
        /**
         * Trigger the order export.
         * The Order was previously exported by "triggerOrderExport". Now we have to update the status.
         * @link https://trello.com/c/P0ohPHrv/41-order-export-nur-wenn-payment-durch-ist-und-order-status-auf-processing-gesetzt-wird#comment-5dce96ffb89afb3d0538efd8
         * */
            try {
                
            // Only trigger when an order enters processing state. https://stackoverflow.com/questions/30357622/trigger-observer-on-magento-order-status-change-events
                $stateProcessing = $order::STATE_PROCESSING; // zB "processing"
                $orderState = $order->getState();

                $msg = "ID $order_id -> OriginalState = ".$order->getOrigData('state').", new State = $orderState";
                $this->orderLog($order_id, $msg);
                
                if ($order->getState() == $stateProcessing && $order->getOrigData('state') != $stateProcessing) {
                    // for DEV we need to see, if the update worked. That we can see by adding the timestamp.
                        if (ENVIRONMENT=="development") {
                            $orderState .= " (".date("d.m.Y H:i:s").")";
                        }
                        
                    // set the data for updateing the order
                        $data = array(
                            "Prepmt_Posting_Description" => $orderState, // @todo: Die Logik lieber im Model handeln und dort das Feld zuweisen.
                        );                    
                        
                    /**
                     * call the model and use the UPDATE method
                     */
                        $this->_orderExport->updateOrder($order_id, $data);

                }
                else {
                    $msg = "No Update will be performed, because the status did not change to Processing. ".__FILE__.__LINE__;
                    $this->orderLog($order_id, $msg);
                }
            
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $msg .= ", ".__FILE__.__LINE__;
                $this->orderLog($order_id, $msg);
                // preprint($msg, __FILE__.__LINE__); die();
                return false;
            }
            
            // preprint(__FILE__, __LINE__); die();

    }

}
