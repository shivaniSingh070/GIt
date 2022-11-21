<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Ulmod\OrderImportExport\Api\ImporterInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Sales\Api\Data\OrderItemInterface;
use Ulmod\OrderImportExport\Model\Catalog\Product as CatalogModel;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Tax\Model\Calculation as CalculationModel;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Item as OrderItemModel;
use Magento\Catalog\Model\Product\Type as ProductType;
    
class OrderItem extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var OrderItemInterfaceFactory
     */
    private $orderItemFactory;

    /**
     * @var CalculationModel
     */
    private $calculation;

    /**
     * @var array
     */
    private $skuEntityIdPairs;

    /**
     * @param CatalogModel $catalogModel
     * @param OrderItemInterfaceFactory $orderItemFactory
     * @param CalculationModel $calculation
     * @param array $excludedFields
     */
    public function __construct(
        CatalogModel $catalogModel,
        OrderItemInterfaceFactory $orderItemFactory,
        CalculationModel $calculation,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->orderItemFactory = $orderItemFactory;
        $this->skuEntityIdPairs = $catalogModel->getSkuEntityIdPairs();
        $this->calculation = $calculation;
    }

    /**
     * @param array $data
     * @param OrderInterface|OrderModel $order
     * @return $this
     */
    public function process(array $data, OrderInterface $order)
    {
        $items = [];

        foreach ($data[ImporterInterface::KEY_PRODUCTS_ORDERED] as $key => $product) {
            /** @var OrderItemInterface|\Magento\Sales\Model\Order\Item $item */
            $item = $this->createItem($product);
            
            $storeId = $order->getStoreId();
            
            $item->setOrder($order);
            $item->setStoreId($storeId);
            $item->setData('key', $key);
            $items[] = $item;

            /** @var OrderItemInterface|OrderItemModel $childItem */
            $childrenItems = $item->getChildrenItems();
            foreach ($childrenItems as $childKey => $childItem) {
                $childItem->setOrder($order);
                $childItem->setStoreId($storeId);
                $childItem->setData('parent_key', $key);
                $childItem->setData('key', $childKey);
                $items[] = $childItem;
            }
        }

        $order->setItems($items);

        return $this;
    }

    /**
     * @param OrderItemInterface|OrderItemModel $item
     */
    private function prepareData(OrderItemInterface $item)
    {
        $this->removeExcludedFields($item);

        $itemPrice = $item->getPrice();
        $baseItemPrice = $item->getBasePrice();
        $qtyItemOrdered = $item->getQtyOrdered();
        $priceInclTax = $item->getPriceInclTax();
        $rowItemTotal = $item->getRowTotal();
        $rowBaseItemTotal = $item->getBaseRowTotal();
        
        foreach ($item->getData() as $key => $value) {
            $item->setOrigData($key, $value);
        }

        $itemSku = $item->getSku();
        if (isset($this->skuEntityIdPairs[$itemSku])) {
            $item->setProductId(
                $this->skuEntityIdPairs[$itemSku]
            );
        }

        if (!$qtyItemOrdered) {
            $item->setQtyOrdered(1);
        }

        $item->setQtyOrdered((float)$qtyItemOrdered);
        $item->setIsVirtual((int)$item->getIsVirtual());
        $item->setQtyBackordered((float)$item->getQtyBackordered());
        $item->setIsQtyDecimal((int)$item->getIsQtyDecimal());
        $item->setRowWeight($item->getRowWeight());
        $item->setFreeShipping((int)$item->getFreeShipping());
        
        
        if ($baseItemPrice === null) {
            $item->setBasePrice($itemPrice);
        }

        $sameBaseAndStore = $itemPrice === $baseItemPrice;
        if ($item->getBaseTaxAmount() === null) {
            if ($sameBaseAndStore) {
                $item->setBaseTaxAmount($item->getTaxAmount());
            } else {
                $amount = $this->calculation->calcTaxAmount(
                    $baseItemPrice,
                    $item->getTaxPercent(),
                    false,
                    true
                );
                $item->setBaseTaxAmount($amount);
            }
        }

        if ($priceInclTax === null) {
            $amount = $this->calculation->calcTaxAmount(
                $itemPrice,
                $item->getTaxPercent(),
                false,
                true
            );
            $item->setPriceInclTax($itemPrice + $amount);
        }

        if ($item->getBasePriceInclTax() === null) {
            if ($sameBaseAndStore) {
                $item->setBasePriceInclTax($priceInclTax);
            } else {
                $amount = $this->calculation->calcTaxAmount(
                    $baseItemPrice,
                    $item->getTaxPercent(),
                    false,
                    true
                );
                $item->setBasePriceInclTax($amount + $baseItemPrice);
            }
        }

        if ($item->getOriginalPrice() === null) {
            $item->setOriginalPrice($itemPrice);
        }

        if ($item->getBaseOriginalPrice() === null) {
            $item->setBaseOriginalPrice($baseItemPrice);
        }

        $item->setBaseDiscountAmount(
            (float)($item->getBaseDiscountAmount() ? $item->getBaseDiscountAmount() : $item->getDiscountAmount())
        );

        if ($rowItemTotal === null) {
            $item->setRowTotal($itemPrice * $qtyItemOrdered);
        }

        if ($rowBaseItemTotal === null) {
            if ($sameBaseAndStore) {
                $item->setBaseRowTotal($rowItemTotal);
            } else {
                $item->setBaseRowTotal($baseItemPrice * $qtyItemOrdered);
            }
        }

        if ($item->getRowTotalInclTax() === null) {
            $amount = $this->calculation->calcTaxAmount(
                $rowItemTotal,
                $item->getTaxPercent(),
                false,
                true
            );
            $item->setRowTotalInclTax($rowItemTotal + $amount);
        }

        if ($item->getBaseRowTotalInclTax() === null) {
            if ($sameBaseAndStore) {
                $item->setBaseRowTotalInclTax($item->getRowTotalInclTax());
            } else {
                $amount = $this->calculation->calcTaxAmount(
                    $rowBaseItemTotal,
                    $item->getTaxPercent(),
                    false,
                    true
                );
                $item->setBaseRowTotalInclTax(
                    $rowBaseItemTotal + $amount
                );
            }
        }

        // weee tax applied
        $baseWeeeTaxAppliedAmount = $item->getBaseWeeeTaxAppliedAmount();
        $weeeTaxAppliedAmount = $item->getWeeeTaxAppliedAmount();
        $weeeTaxAppliedRowAmount = $item->getWeeeTaxAppliedRowAmount();
        
        if ($baseWeeeTaxAppliedAmount === null && $sameBaseAndStore) {
            $item->setBaseWeeeTaxAppliedAmount($weeeTaxAppliedAmount);
        }

        if ($weeeTaxAppliedRowAmount === null) {
            $item->setWeeeTaxAppliedRowAmount(
                $weeeTaxAppliedAmount * $qtyItemOrdered
            );
        }

        if ($item->getBaseWeeeTaxAppliedRowAmnt() === null) {
            if ($sameBaseAndStore) {
                $item->setBaseWeeeTaxAppliedRowAmnt(
                    $weeeTaxAppliedRowAmount
                );
            } else {
                $item->setBaseWeeeTaxAppliedRowAmnt(
                    $baseWeeeTaxAppliedAmount * $qtyItemOrdered
                );
            }
        }

        if ($item->getProductType() === ProductType::TYPE_BUNDLE) {
            $keysArray = [
                OrderItemInterface::PRICE_INCL_TAX      => [],
                OrderItemInterface::BASE_PRICE_INCL_TAX => []
            ];

            foreach ($item->getChildrenItems() as $childItem) {
                $childQty = $childItem->getQtyOrdered();
                foreach ($keysArray as $key => $value) {
                    $keysArray[$key][] = $childQty * $this->calculation->round(
                        $childItem->getData($key)
                    );
                }
            }

            foreach ($keysArray as $key => &$array) {
                $item->setData($key, array_sum($array));
            }

            $keysArray = [
                OrderItemInterface::ROW_TOTAL_INCL_TAX      => [],
                OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX => []
            ];

            foreach ($item->getChildrenItems() as $childItem) {
                foreach ($keysArray as $key => $value) {
                    $keysArray[$key][] = (float)$childItem->getData($key);
                }
            }

            foreach ($keysArray as $key => &$array) {
                $item->setData($key, array_sum($array));
            }
        }

        $productOptions = $item->getData(ImporterInterface::KEY_PRODUCT_OPTIONS);
        if (!is_array($productOptions)) {
            $item->setData(
                ImporterInterface::KEY_PRODUCT_OPTIONS,
                []
            );
        }

        // $productOptions = $item->getData(ImporterInterface::KEY_PRODUCT_OPTIONS);
        if ($productOptions) {
            $itemOptions = [];
            foreach ($productOptions as &$option) {
                $label = isset($option['label']) ? $option['label'] : null;
                $value = isset($option['value']) ? $option['value'] : null;
                $itemOptions[] = [
                    'label' => $label,
                    'value' => $value,
                    'print_value' => $value,
                    'option_id' => '',
                    'option_type' => 'field',
                    'option_value' => $value,
                    'custom_view' => false,
                ];
            }

            $item->setData(ImporterInterface::KEY_PRODUCT_OPTIONS, $itemOptions);
        }
    }

    /**
     * @param array $data
     * @return OrderItemInterface|OrderItemModel
     */
    private function createItem(array $data)
    {
        /** @var OrderItemInterface|OrderItemModel $item */
        $item = $this->orderItemFactory->create();
        
        $item->addData($data);

        $options = [];

        if (isset($data[ImporterInterface::KEY_PRODUCT_BUNDLE_ITEMS]) &&
            $item->getProductType() === Type::TYPE_BUNDLE
        ) {
            $bundleOptions = [];
            foreach ($data[ImporterInterface::KEY_PRODUCT_BUNDLE_ITEMS] as $childData) {
                /** @var OrderItemInterface|OrderItemModel $childItem */
                $childItem = $this->createItem($childData);
                
                $childItem->setParentItem($item);

                $itemName = $childItem->getName();
                $qtyOrdered = $childItem->getQtyOrdered();
                $itemPrice = $childItem->getPrice();
                $bundleOptions[] = [
                    'label' => $itemName,
                    'value' => [
                        [
                            'title' => $itemName,
                            'qty'   => $qtyOrdered,
                            'price' => $itemPrice
                        ]
                    ]
                ];

                end($bundleOptions);
                $key = key($bundleOptions);

                $options['info_buyRequest']['bundle_option'][] = $key;
                $options['info_buyRequest']['bundle_option_qty'][] = $qtyOrdered;
            }

            $options['product_calculations'] = AbstractType::CALCULATE_CHILD;
            $options['bundle_options'] = $bundleOptions;
        }

        $this->prepareData($item);

        $options['options'] = $item->getData(
            ImporterInterface::KEY_PRODUCT_OPTIONS
        );
        
        $options['info_buyRequest']['options'] = $item->getData(
            ImporterInterface::KEY_PRODUCT_OPTIONS
        );

        $item->setProductOptions($options);

        return $item;
    }
}
