<?php
/* Creted Helper for module
 * @package  Pixelmechanics_Engelsrufer
 * @module   Engelsrufer
 * Used for global functions required throughtout the website
 * Created by AA 24.04.2019
*/
 
namespace Pixelmechanics\Engelsrufer\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
   /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
   protected $_storeManager;

   /**
    * @var \Magento\Store\Model\UrlInterface
    */
   protected $_urlInterface;
   
   /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /*
     * @var Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /*
     * @var Magento\Tax\Model\Calculation\Rate
     */
    protected $resource;
    

   /**
     * [__construct description].
     *
     * @param \Magento\Framework\App\Helper\Context          $context
     * @param \Magento\Store\Model\StoreManagerInterface     $storeManager
     * @param \Magento\Store\Model\UrlInterface              $urlInterface
     * @param \Magento\Customer\Model\Session                $session
     */    
 
   public function __construct( 
        \Magento\Framework\App\Helper\Context $context,       
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,   
        \Magento\Customer\Model\Session $session, 
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    )
    {        
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->_session = $session;
        $this->_scopeConfig = $scopeConfig;
        $this->httpContext = $httpContext;
        $this->checkoutSession = $checkoutSession;
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * get Pub media Url
     * @return string
     */

    public function getPubMediaUrl()
    {          
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    
    /**
     * get customer session
     * @return boolean
     * Updated by AA, 25.04.2019
     */
 
    public function getCustomerSession()
    {          
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }
    
    /**
     * get Base URL
     * @return string
     * Updated by AA, 14.06.2019
     */
    
    public function getBaseUrl(){
       return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
    
    /**
     * get store config for the opengraph 
     * Updated by NA, 25.04.2019
     */
    public function getStoreConfig($object){
        
        $loadConfiguration = $this->_scopeConfig->getValue($object, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $loadConfiguration;
    }

    /**
     * get store code en|de for the paypal log on pdp page 
     * Updated by NA, 19.11.2019
     * trello: https://trello.com/c/lzbBxhn7/123-044funktionen-produktdetailseiten-product-detail-page-cross-upselling#comment-5da9c674ef9c957ac0940929
     */

     public function getStoreCode(){
        return $this->_storeManager->getStore()->getCode();
    }

    /*
    * By AA on 8.9.2021
    * https://trello.com/c/5BJVIHn6/
    * Get last order details from checkout session
    */
    public function getOrderDetails()
    {
        $orderData = [];
        $order = $this->checkoutSession->getLastRealOrder();
        $orderData['transactionId'] = $order->getIncrementId();
        $orderData['currency'] = $order->getOrderCurrencyCode();
        $orderData['transactionAmount'] = $order->getGrandTotal();
        return $orderData;
    }

    /*
    * Get tax rate id by code
    * By PM AJ on 05.04.2022
    * @link: https://trello.com/c/NMHf5Em4
    */
    public function getTaxRates($code)
    {   
        if($code=='')
            return;

        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('tax_calculation_rate');

        $selectRate = 'SELECT tax_calculation_rate_id from '.$tableName.' WHERE code = "'.$code.'"';
        return  $connection->fetchOne($selectRate);
    }

    
}
