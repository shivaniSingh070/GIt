<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Plugin\SalesRule\Model;

use Amasty\Mostviewed\Model\OptionSource\DiscountType;

/**
 * Class RulesApplier
 * @package Amasty\Mostviewed\Plugin\SalesRule\Model
 */
class RulesApplier
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem
     */
    private $item;

    /**
     * @var array
     */
    private $itemData;

    /**
     * @var null|array
     */
    private $productsInCart = null;

    /**
     * @var null|array
     */
    private $productsQty = null;

    /**
     * @var \Magento\SalesRule\Model\Validator
     */
    private $validator;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->packRepository = $packRepository;
        $this->validator = $validator;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\SalesRule\Model\RulesApplier $subject
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules
     * @param bool $skipValidation
     * @param mixed $couponCode
     *
     * @return array
     */
    public function beforeApplyRules($subject, $item, $rules, $skipValidation, $couponCode)
    {
        $this->item = $item;
        $this->itemData = [
            'itemPrice'         => $this->validator->getItemPrice($item),
            'baseItemPrice'     => $this->validator->getItemBasePrice($item),
            'itemOriginalPrice' => $this->validator->getItemOriginalPrice($item),
            'baseOriginalPrice' => $this->validator->getItemBaseOriginalPrice($item)
        ];

        return [$item, $rules, $skipValidation, $couponCode];
    }

    /**
     * @param \Magento\SalesRule\Model\RulesApplier $subject
     * @param array $appliedRuleIds
     *
     * @return array
     */
    public function afterApplyRules($subject, $appliedRuleIds)
    {
        $bundleRuleApplied = $this->checkForChilds();
        $bundleRuleApplied |= $this->checkForParents();
        if ($bundleRuleApplied) {
            $appliedRuleIds = [];
        }

        return $appliedRuleIds;
    }

    /**
     * @return array
     */
    private function getProductsInCart()
    {
        if ($this->productsInCart === null) {
            $this->productsInCart = [];
            foreach ($this->item->getAddress()->getAllItems() as $quoteItem) {
                $this->productsInCart[] = $quoteItem->getProductId();
                $this->productsQty[$quoteItem->getProductId()] = isset($this->productsQty[$quoteItem->getProductId()]) ?
                    $this->productsQty[$quoteItem->getProductId()] + $quoteItem->getTotalQty() :
                    $quoteItem->getTotalQty();
            }
        }

        return $this->productsInCart;
    }

    /**
     * @return bool
     */
    private function checkForChilds()
    {
        $result = false;
        $packs = $this->packRepository->getPacksByChildProductsAndStore(
            [$this->item->getProductId()],
            $this->item->getStoreId()
        );
        if ($packs) {
            /** @var \Amasty\Mostviewed\Model\Pack $pack */
            foreach ($packs as $pack) {
                $parentProductsInCart = array_intersect(
                    $pack->getParentIds(),
                    $this->getProductsInCart()
                );

                $parentProductsInCart = array_diff($parentProductsInCart, [$this->item->getProductId()]);
                if (!empty($parentProductsInCart)) {
                    $this->applyPackRule($pack, $this->retrieveProductsQty($parentProductsInCart));
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function checkForParents()
    {
        $result = false;
        $packs = $this->packRepository->getPacksByParentProductsAndStore(
            [$this->item->getProductId()],
            $this->item->getStoreId()
        );
        if ($packs) {
            /** @var \Amasty\Mostviewed\Model\Pack $pack */
            foreach ($packs as $pack) {
                if ($pack->getApplyForParent()) {
                    $childProductIds = array_intersect(
                        explode(',', $pack->getProductIds()),
                        $this->getProductsInCart()
                    );

                    $childProductIds = array_diff($childProductIds, [$this->item->getProductId()]);
                    if (!empty($childProductIds)) {
                        $this->applyPackRule($pack, $this->retrieveProductsQty($childProductIds));
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param \Amasty\Mostviewed\Model\Pack $pack
     * @param null|int $countCanApplied
     */
    private function applyPackRule($pack, $countCanApplied = null)
    {
        $amount = 0;
        $baseAmount = 0;
        $qty = $countCanApplied && $countCanApplied < $this->item->getTotalQty() ?
            $countCanApplied :
            $this->item->getTotalQty();
        switch ($pack->getDiscountType()) {
            case DiscountType::FIXED:
                $amount = $qty * $this->priceCurrency->convert(
                    $pack->getDiscountAmount(),
                    $this->item->getQuote()->getStore()
                );
                $baseAmount = $qty * $pack->getDiscountAmount();
                break;
            case DiscountType::PERCENTAGE:
                $amount = $qty * $this->itemData['itemPrice'] *
                    $pack->getDiscountAmount() / 100;
                $baseAmount = $qty * $this->itemData['baseItemPrice'] *
                    $pack->getDiscountAmount() / 100;
                $amount = $this->priceCurrency->round($amount);
                $baseAmount = $this->priceCurrency->round($baseAmount);
                break;
        }
        $amount = min($amount, $this->itemData['itemPrice']);
        $baseAmount = min($baseAmount, $this->itemData['baseItemPrice']);
        $this->item->setDiscountAmount($amount);
        $this->item->setBaseDiscountAmount($baseAmount);
    }

    /**
     * @param array $productIds
     *
     * @return int
     */
    private function retrieveProductsQty($productIds)
    {
        $productQty = 0;
        foreach ($productIds as $productId) {
            $productQty += $this->productsQty[$productId];
        }

        return $productQty;
    }
}
