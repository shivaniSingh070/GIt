<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
/** 
 * Override the Messages class like customer requirement in card 
 * --> not showing the massage after the customer placed order of the Bundle Packs
 * 
 * created 07.06.2022 by HA
 * trello link https://trello.com/c/wAHcWVmT/370-wg-bundle?filter=member:constantinschurer
 */

namespace Pixelmechanics\Mostviewed\Block\Checkout\Cart;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class Messages
 * @package Amasty\Mostviewed\Block\Checkout\Cart
 */
class Messages extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Mostviewed::bundle/cart_message.phtml';

    /**
     * @var \Amasty\Mostviewed\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var null|array
     */
    private $productsInCart = null;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $catalogProductVisibility;

    public function __construct(
        Template\Context $context,
        \Amasty\Mostviewed\Helper\Config $config,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        PriceCurrencyInterface $priceCurrency,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->session = $session;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->packRepository = $packRepository;
        $this->priceCurrency = $priceCurrency;
        $this->collectionFactory = $collectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->isEnabled()) {
            return parent::toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return $this->config->isMessageInCartEnabled() && $this->getProductsInCart();
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getMessage()
    {
        $data = [];

        $packs = $this->packRepository->getPacksByParentProductsAndStore(
            $this->getProductsInCart(),
            $this->_storeManager->getStore()->getId()
        );
        $data = $this->validatePacks($data, $packs, true);

        $packs = $this->packRepository->getPacksByChildProductsAndStore(
            $this->getProductsInCart(),
            $this->_storeManager->getStore()->getId()
        );
        $data = $this->validatePacks($data, $packs, false);

        if ($data) {
            $data = $data[array_rand($data)];//get random message
        }

        return $data;
    }

    /**
     * @param array $packs
     * @param array $data
     * @param bool $isParent
     *
     * @return array
     */
    private function validatePacks($data, $packs, $isParent)
    {
        if ($packs) {
            /** @var \Amasty\Mostviewed\Model\Pack $pack */
            foreach ($packs as $pack) {
                $productsToCheck = $isParent ? explode(',', $pack->getProductIds()) : $pack->getParentIds();
                $missedProducts = array_diff(
                    $productsToCheck,
                    $this->getProductsInCart()
                );
                /**
                 * add $TocheckCount,$ProductInCardCount,$missedProductsCount three counter to make check if the bundle packs used or not 
                 */
               $TocheckCount = count($productsToCheck);
               $ProductInCardCount = count($this->getProductsInCart());
               $missedProductsCount= count($missedProducts);
                if (!empty($missedProducts && $TocheckCount <= $missedProductsCount)) {
                    $data[] = $this->dataObjectFactory->create(
                        [
                            'data' => [
                                'products' => $missedProducts,
                                'pack_id'  => $pack->getPackId(),
                                'discount' => $this->generateDiscount($pack),
                                'message'  => $pack->getCartMessage()
                            ]
                        ]
                    );
                }
            }
        }

        return $data;
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function convertMessage($data)
    {
        $message = $this->escapeHtml(trim($data->getMessage()));
        if ($message) {
            preg_match_all('@\{(.+?)\}@', $message, $matches);
            if (isset($matches[1]) && !empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $result = '';
                    switch ($match) {
                        case 'product_names':
                            $result = $this->generateNamesContent($data->getProducts());
                            break;
                        case 'discount_amount':
                            $result = $data->getDiscount();
                            break;
                    }

                    if (!$result) {
                        $message = '';
                        break;
                    }
                    $message = str_replace('{' . $match . '}', $result, $message);
                }
            }
        }

        return $message;
    }

    /**
     * @param array $productIds
     *
     * @return array|string
     */
    protected function generateNamesContent($productIds)
    {
        $collection = $this->collectionFactory->create()
            ->addIdFilter($productIds)
            ->addAttributeToSelect(['status', 'name'], 'left')
            ->addStoreFilter()
            ->addUrlRewrite();

        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        // @codingStandardsIgnoreStart
        $result = [];
        foreach ($collection as $product) {
            $result[] = sprintf(
                '<a class="product-link" href="%s" title="%s">%s</a>',
                $product->getProductUrl(),
                $product->getName(),
                $product->getName()
            );
        }
        // @codingStandardsIgnoreEnd
        $result = implode(', ', $result);

        return $result;
    }

    /**
     * @param PackInterface $pack
     *
     * @return string
     */
    private function generateDiscount(PackInterface $pack)
    {
        $discount = $pack->getDiscountAmount();

        if ($pack->getDiscountType() == DiscountType::PERCENTAGE) {
            $result = $discount . '%';
        } else {
            $result = $this->priceCurrency->format($discount);
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getProductsInCart()
    {
        if ($this->productsInCart === null) {
            $this->productsInCart = [];
            foreach ($this->session->getQuote()->getAllItems() as $quoteItem) {
                $this->productsInCart[] = $quoteItem->getProductId();
            }
        }

        return $this->productsInCart;
    }
}
