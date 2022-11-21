<?php
namespace Pixelmechanics\ExportOrder\Controller\Adminhtml\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;


/**
 * @description: Add the Creditmemo Export Controller.
 * @author: AY
 * @date: 25-oct-2021
 * @Trello: https://trello.com/c/7yfEDXmg
 */
class Export extends \Magento\Backend\App\Action {

    protected $redirectUrl = '*/*/';
    protected $filter;
    protected $collectionFactory;
    protected $orderRepository;

    /**
     * @var \Pixelmechanics\ExportOrder\Model\Order\Creditmemo\Export
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
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $orderCollectionFactory
     * @param \Pixelmechanics\ExportOrder\Model\Order\Creditmemo\Export $orderExport
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $orderCollectionFactory,
        \Pixelmechanics\ExportOrder\Model\Order\Creditmemo\Export $orderExport,
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

        // Loop through all creditmemos
        foreach ($collection->getItems() as $creditMemo) {
            // get order state from $order

            // Export order if order state is "processing" and $doExportProcessingOnly is "yes" in admin
            // BUT: In the backend, we always want to export the order, because this is a manual action and the user knows, what he is doing.
            // See https://bitbucket.org/pixelmechanics/engelsrufer-relaunch/pull-requests/559/order-save-event/diff#comment-124596780
            // $orderState = $order->getState();
            // if ($doExportProcessingOnly && $orderState == "processing") {
            if ($this->_orderExport->exportOrder($creditMemo) !== false ) {
                $succeeded++;
            } else {
                $failed++;
            }
        }

        if ($failed>0) {
            $this->messageManager->addErrorMessage($failed." ".__("failed"));
        }
        if ($succeeded>0) {
            $this->messageManager->addSuccessMessage($succeeded." ".__(" Creditmemo's successfully exported"));
        }


        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->filter->getComponentRefererUrl() ?: 'sales/*/');
        return $resultRedirect;
    }

}
