<?php
namespace Pixelmechanics\ExportOrder\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;


/**
 * @description: Add the ExportOrder Order Export Controller.
 * @author: AA
 * @date: 19.06.2019
 * @Trello: https://trello.com/c/7yfEDXmg
 */
class Export extends \Magento\Backend\App\Action {

    protected $redirectUrl = '*/*/';
    protected $filter;
    protected $collectionFactory;
    protected $orderRepository;

    /**
     * @var \Pixelmechanics\ExportOrder\Model\Order\Export
     */
    protected $_orderExport;
    
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;
    
    /**
     * Parameters added here are automatically used by the dependence-injection (di).
     * Use "setup:di:compile" in the shell, when updating anything here.
     * @param Context $context
     * @param Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Pixelmechanics\ExportOrder\Model\Order\Export $orderExport
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context, 
        Filter $filter, 
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Pixelmechanics\ExportOrder\Model\Order\Export $orderExport,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $orderCollectionFactory;
        $this->_orderExport = $orderExport;
        $this->_scopeConfig = $scopeConfig;
    }

    
    /**
     * Loops through all selected orders and creates the Files.
     * @return type
     */
    public function execute() {
        // Get order ids from selected orders
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        
        //get value from system -> configuration -> Pixelmechanics -> Navision Configuration -> Export only when State changed to processing?
        $doExportProcessingOnly = trim($this->_scopeConfig->getValue('pixelmechanics_configuration/navisionExport/doExportProcessingOnly'));
       
        // Output the number of successfull and failed exports later.
            $succeeded = 0;
            $failed = 0;
            
        // Loop through all orders
        foreach ($collection->getItems() as $order) {
            $orderID = $order->getEntityId(); 
            // get order state from $order
            
            // Export order if order state is "processing" and $doExportProcessingOnly is "yes" in admin
            // BUT: In the backend, we always want to export the order, because this is a manuell action and the user knows, what he is doing.
            // See https://bitbucket.org/pixelmechanics/engelsrufer-relaunch/pull-requests/559/order-save-event/diff#comment-124596780
                // $orderState = $order->getState();
                // if ($doExportProcessingOnly && $orderState == "processing") {
                if ($this->_orderExport->exportOrder($orderID) !== false ) {
                    $succeeded++;
                } else {
                    $failed++;
                }
                // }
        }
        
        /**
         * updated the success message
         * @author AA on 16.10.2019 
         * @link https://trello.com/c/7yfEDXmg/17-api-09-order-export-magento-navision-export-von-bestellinformationen-mit-kundeninformationen-navision-teil-2#comment-5da70e202f30e3772891ee32
         * @link https://www.rakeshjesadiya.com/display-success-and-error-messages-using-magento-2/
         */
            if ($failed>0) {
                $this->messageManager->addErrorMessage($failed." ".__("failed"));
            }
            if ($succeeded>0) {
                // $this->messageManager->addSuccessMessage($succeeded." ".__("successfully exported"));
            }

        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->filter->getComponentRefererUrl() ?: 'sales/*/');
        return $resultRedirect;
    }
    
}
