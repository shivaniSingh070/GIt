<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Block\Product;

use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Amasty\Mostviewed\Model\Pack;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Amasty\Mostviewed\Api\Data\PackInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Helper\ImageFactory as HelperFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Class BundlePack
 * @package Amasty\Mostviewed\Block\Product
 */
class BundlePack extends \Magento\Catalog\Block\Product\AbstractProduct implements IdentityInterface
{
    const RELATED_IMAGE_ID = 'related_products_content';

    /**
     * @var array
     */
    private $bundles = [];

    /**
     * @var string
     */
    protected $_template = 'Amasty_Mostviewed::bundle/pack.phtml';

    /**
     * @var \Amasty\Mostviewed\Helper\Config
     */
    protected $config;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Mostviewed\Helper\Price
     */
    private $priceHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var HelperFactory
     */
    private $helperFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Amasty\Mostviewed\Helper\Config $config,
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Mostviewed\Helper\Price $priceHelper,
        PriceCurrencyInterface $priceCurrency,
        HelperFactory $helperFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->packRepository = $packRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->jsonEncoder = $jsonEncoder;
        $this->priceHelper = $priceHelper;
        $this->priceCurrency = $priceCurrency;
        $this->helperFactory = $helperFactory;
        $this->sessionFactory = $sessionFactory;
        $this->stockHelper = $stockHelper;
        $this->request = $request;
        $this->localeFormat = $localeFormat;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $name = $this->getNameInLayout();
        $position = $this->config->getBlockPosition();
        if (strpos($name, $position) !== false) {
            if ($this->isBundlePacksExists()) {
                return $this->getParentHtml();
            }
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isCheckoutPage()
    {
        return $this->request->getFullActionName() === 'checkout_cart_index';
    }

    /**
     * @return string
     */
    public function getParentHtml()
    {
        return parent::toHtml();
    }

    /**
     * @return bool
     */
    public function isBundlePacksExists()
    {
        $product = $this->getProduct();
        if (!$product || !$product->isSaleable()) {
            return false;
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $bundles = $this->packRepository->getPacksByParentProductsAndStore(
            [$product->getId()],
            $storeId
        );

        if ($bundles) {
            /** @var PackInterface $pack */
            foreach ($bundles as $key => $pack) {
                $currentCustomerGroup = $this->getCustomerSession()->getCustomerGroupId() ?: 0;
                $customerGroups = $pack->getCustomerGroupIds();
                $customerGroups = explode(',', $customerGroups);
                if (!in_array($currentCustomerGroup, $customerGroups)) {
                    unset($bundles[$key]);
                }
            }

            if ($bundles) {
                $this->bundles = $bundles;

                return true;
            }

        }

        return false;
    }

    /**
     * @param $product
     *
     * @return string
     */
    public function getImageUrl($product)
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->helperFactory->create()
            ->init($product, self::RELATED_IMAGE_ID);

        return $helper->getUrl();
    }

    /**
     * @param string|array $childIds
     *
     * @return array
     */
    public function getProductItems($childIds)
    {
        if (!is_array($childIds)) {
            $childIds = explode(',', $childIds);
        }

        /** @var ProductCollection $products */
        $collection = $this->collectionFactory->create()
            ->addIdFilter($childIds)
            ->addFieldToFilter('type_id', ['nin' => [Grouped::TYPE_CODE, 'giftcard']])
            ->addIdFilter($this->getProduct()->getId(), true);

        $collection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        $this->_addProductAttributesAndPrices($collection);
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $this->stockHelper->addIsInStockFilterToCollection($collection);

        $productItems = [];
        foreach ($collection as $product) {
            $product->setDoNotUseCategoryId(true);
            $productItems[$product->getId()] = $product;
        }

        /* set correct sort order*/
        foreach ($childIds as $key => $childId) {
            if (isset($productItems[$childId])) {
                $childIds[$key] = $productItems[$childId];
            } else {
                unset($childIds[$key]);
            }
        }
        if (!empty($childIds)) {
            array_unshift($childIds, $this->getProduct());//add main product as first
        }

        return $childIds;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode(
            [
                'url' => $this->getUrl('ammostviewed/cart/add')
            ]
        );
    }

    /**
     * @param PackInterface $pack
     * @param array|ProductCollection $items
     *
     * @return array
     */
    public function getPackJsonConfig(PackInterface $pack, $items)
    {
        $data = [
            'product_id' => (int)$this->getProduct()->getId(),
            'discount_amount' => (float)$pack->getDiscountAmount(),
            'parent_price' => (float)$this->priceCurrency->convert(
                $this->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
            ),
            'discount_type' => (int)$pack->getDiscountType(),
            'apply_for_parent' => (bool)$pack->getApplyForParent(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'products' => []
        ];
        foreach ($items as $key => $item) {
            if ($key === 0) {
                continue; //skip parent product
            }

            $data['products'][$item->getId()] = (float)$this->priceCurrency->convert(
                $item->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
            );
        }

        return $data;
    }

    /**
     * @param PackInterface $pack
     * @param $isParent
     *
     * @return string
     */
    public function getProductDiscount(PackInterface $pack, $isParent)
    {
        $discount = $pack->getDiscountAmount();

        if ($pack->getDiscountType() == DiscountType::PERCENTAGE) {
            $result = $discount . '%';
        } else {
            $result = '-' . $this->priceOutput($discount);
        }

        if ($isParent && !$pack->getApplyForParent()) {
            $result = '';
        }

        return $result;
    }

    /**
     * @param $price
     *
     * @return string
     */
    public function priceOutput($price)
    {
        return $this->priceCurrency->format($price);
    }

    /**
     * @param $config
     *
     * @return array
     */
    public function getDiscountResult($config)
    {
        $parentPrice = $config['parent_price'];
        $oldPrice = $parentPrice;
        $newPrice = $config['apply_for_parent'] ? $this->applyDiscount($parentPrice, $config) : $parentPrice;

        foreach ($config['products'] as $finalItemPrice) {
            $oldPrice += $finalItemPrice;
            $newPrice += $this->applyDiscount($finalItemPrice, $config);
        }

        return [
            'final_price' => $newPrice,
            'discount' => $oldPrice - $newPrice
        ];
    }

    /**
     * @param $price
     * @param $config
     *
     * @return float|int
     */
    private function applyDiscount($price, $config)
    {
        if ($config['discount_type'] == DiscountType::FIXED) {
            $price = ($price >= $config['discount_amount']) ? $price - $config['discount_amount'] : 0;
        } else {
            $price = $this->priceCurrency->round($price * (100 - $config['discount_amount']) / 100);
        }

        return $price;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function encode($data)
    {
        return $this->jsonEncoder->encode($data);
    }

    /**
     * @return array
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->isBundlePacksExists()) {
            foreach ($this->bundles as $bundle) {
                $identities[] = Pack::CACHE_TAG . '_' . $bundle->getPackId();
                foreach (explode(',', $bundle->getProductIds()) as $productId) {
                    $identities[] = Product::CACHE_TAG . '_' . $productId;
                }
            }
        }

        return $identities;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }

    /**
     * @param $bundles
     *
     * @return $this
     */
    public function setBundles($bundles)
    {
        $this->bundles = $bundles;

        return $this;
    }

    /**
     * @param $product
     *
     * @return $this
     */
    public function setProduct($product)
    {
        $this->setData('product', $product);

        return $this;
    }
}
