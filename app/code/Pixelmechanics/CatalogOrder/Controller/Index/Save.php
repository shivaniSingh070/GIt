<?php
/**
 * @author: Noshad Ali
 * @package: Pixelmechanics_CatalogOrder
 * @description: to send emails and save the requested data to db.
 * trello: https://trello.com/c/UyuL2qfu/
 */

namespace Pixelmechanics\CatalogOrder\Controller\Index;

use Magento\Framework\App\Action\Context;
use Pixelmechanics\CatalogOrder\Model\CatalogOrderFactory;
use Magento\Framework\Filesystem;


class Save extends \Magento\Framework\App\Action\Action
{
	/**
     * @var CatalogOrder
     */
    protected $_catalogorder;

     /*
      @var \Pixelmechanics\Engelsrufer\Helper\Data
     */
    protected $_helper;

    protected $_logLoggerInterface;

    protected $senderResolver;

    /*
     * @var Filesystem
    */

    private $fileSystem;

    public function __construct(
		Context $context,
        CatalogOrderFactory $catalogorder,
        \Pixelmechanics\Engelsrufer\Helper\Data $helper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Filesystem $fileSystem,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Email\Model\Template\SenderResolver $senderResolver 
    ) {
        $this->_catalogorder = $catalogorder;
        $this->_helper = $helper;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver;
        $this->messageManager = $context->getMessageManager();
        $this->_logLoggerInterface = $loggerInterface;
        $this->fileSystem = $fileSystem;
        parent::__construct($context);
    }
	public function execute()
    {
        try
        {
            $data = $this->getRequest()->getParams();
        	$catalogorder = $this->_catalogorder->create();
            $catalogorder->setData($data);

             $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/catalog_form.log');
             $logger = new \Zend\Log\Logger();
             $logger->addWriter($writer);

             $replyTo = $data['email'];
             $replyToName = $data['name'];

             $sender = $this->senderResolver->resolve($this->_helper->getStoreConfig("contact/catalog_order_email/from"));
             $this->_inlineTranslation->suspend();

                    $sentToEmail = $replyTo;
                    $templateId = $this->_helper->getStoreConfig('contact/catalog_order_email/email_template');
                    $mediaPath = $this->_helper->getPubMediaUrl();

                    $orderPdfName = $this->_helper->getStoreConfig('contact/catalog_order_email/order_file_upload');
                   
            
                    $pdfFileUrl = $mediaPath.'catalog_order/'.$orderPdfName;
                    $data['pdf']= $pdfFileUrl;

                    $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                        )
                    ->setTemplateVars($data)
                    ->setFrom($sender)
                    ->addTo($sentToEmail)
                    ->setReplyTo($replyTo, $replyToName)
                    ->getTransport();
                         
                        $transport->sendMessage();     
                        $this->_inlineTranslation->resume();


                if($catalogorder->save()){
                    $this->messageManager->addSuccessMessage(__('Thank you for your inquiry. Our catalog will be sent to your registered e-mail address.'));
                }else{
                    $this->messageManager->addErrorMessage(__('There is some issue, Try after some time.'));
                }
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('catalogorder');
                return $resultRedirect;

        } catch(\Exception $e){   
                $catalogorder->save();
                $this->messageManager->addError($e->getMessage());
                $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('catalogorder');
            return $resultRedirect;
              
            }
    } 
}
