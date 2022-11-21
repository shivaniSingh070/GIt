<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Ajaxcart\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Currently selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;


    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $prdImageHelper;

    /**
     * @var \Tigren\Ajaxsuite\Helper\Data
     */
    protected $_ajaxsuiteHelper;

    protected $_stockState;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CustomerSession $customerSession
     * @param LayoutFactory $layoutFactory
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerSession $customerSession,
        LayoutFactory $layoutFactory,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $_stockState,
        \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_layoutFactory = $layoutFactory;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_objectManager = $objectManager;
        $this->prdImageHelper = $imageHelper;
        $this->_stockState = $_stockState;
        $this->_ajaxsuiteHelper = $ajaxsuiteHelper;
        parent::__construct($context);
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    /**
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionsPopupHtml($product)
    {
        $layout = $this->_layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->addHandle('ajaxcart_options_popup')->load();
        $layout->generateXml();
        $layout->generateElements();
        $result = $layout->getOutput();
        $layout->__destruct();
        return $result;
    }

    /**
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSuccessHtml($product)
    {
        $layout = $this->_layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->addHandle('ajaxcart_success_message')->load();
        $layout->generateXml();
        $layout->generateElements();
        $result = $layout->getOutput();
        $layout->__destruct();
        return $result;
    }

    /**
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getErrorHtml($product)
    {
        $layout = $this->_layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->addHandle('ajaxcart_error_message')->load();
        $layout->generateXml();
        $layout->generateElements();
        $result = $layout->getOutput();
        $layout->__destruct();
        return $result;
    }

    /**
     * @return bool
     */
    public function isEnabledAjaxcart()
    {
        return (bool)$this->scopeConfig->getValue(
            'ajaxcart/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * @return int
     */
    public function getPopupTTL()
    {
        if ($this->isEnabledPopupTTL()) {
            return (int)$this->scopeConfig->getValue(
                'ajaxcart/general/popupttl',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeId
            );
        }
        return 0;
    }

    /**
     * @return bool
     */
    public function isEnabledPopupTTL()
    {
        return (bool)$this->scopeConfig->getValue(
            'ajaxcart/general/enabled_popupttl',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * @return string
     */
    public function getAjaxCartInitOptions()
    {
        $optionsAjaxsuite = $this->_jsonDecoder->decode($this->_ajaxsuiteHelper->getAjaxSuiteInitOptions());
        $options = [
            'ajaxCart' => [
                'addToCartUrl' => $this->_getUrl('ajaxcart/cart/showPopup'),
                'addToCartInWishlistUrl' => $this->_getUrl('ajaxcart/wishlist/showPopup'),
                'checkoutCartUrl' => $this->_getUrl('checkout/cart/add'),
                'wishlistAddToCartUrl' => $this->_getUrl('wishlist/index/cart'),
                'addToCartButtonSelector' => $this->getAddToCartButtonSelector()
            ]
        ];

        return $this->_jsonEncoder->encode(array_merge($optionsAjaxsuite, $options));
    }

    /**
     * @return string
     */
    public function getAddToCartButtonSelector()
    {
        $class = $this->getScopeConfig('ajaxcart/general/addtocart_btn_class');
        if (empty($class)) {
            $class = 'add-to-cart';
        }
        return 'button.' . $class;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getScopeConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeId);
    }

    /**
     * @param $icon
     * @return string
     */
    public function getAjaxSidebarInitOptions($icon)
    {
        $options = [
            'icon' => $icon,
            'texts' => [
                'loaderText' => __('Loading...'),
                'imgAlt' => __('Loading...')
            ]
        ];

        return $this->_jsonEncoder->encode($options);
    }

    /**
     * @param $price
     * @return int
     */
    public function getPriceWithCurrency($price)
    {
        if ($price) {
            return $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency($price, true, false);
        }
        return 0;
    }

    /**
     * @param $product
     * @param $size
     * @return string
     */
    public function getProductImageUrl($product, $size)
    {
        $imageSize = 'product_page_image_' . $size;
        if ($size == 'category') {
            $imageSize = 'category_page_list';
        }
        $imageUrl = $this->prdImageHelper->init($product, $imageSize)
            ->keepAspectRatio(true)
            ->keepFrame(false)
            ->getUrl();
        return $imageUrl;
    }

    /**
     * @return mixed
     */
    public function getEnableAjaxShoppingCart()
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/general/ajax_update_cart_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeId);
    }

    public function getStockState($product)
    {
        return $this->_stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
    }
}
