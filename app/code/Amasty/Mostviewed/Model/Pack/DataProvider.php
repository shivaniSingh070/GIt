<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Pack;

use Amasty\Mostviewed\Controller\Adminhtml\Pack\Edit;
use Amasty\Mostviewed\Model\Pack;
use Magento\Framework\App\Request\DataPersistorInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;

/**
 * Class DataProvider
 * @package Amasty\Mostviewed\Model\Pack
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var \Amasty\Mostviewed\Helper\Price
     */
    private $priceModifier;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\UrlInterface $urlBuilder,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        ProductCollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        \Amasty\Mostviewed\Helper\Price $priceModifier,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $coreRegistry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->priceModifier = $priceModifier;
    }

    /**

     * @return array
     */
    public function getData()
    {
        $result = parent::getData();

        $current = $this->getCurrentPack();
        if ($current && $current->getPackId()) {
            $data = $this->convertProductsData($current->getData());
            $result[$current->getPackId()] = $data;
        } else {
            $data = $this->getSavedPack();
            if (!empty($data)) {
                /** @var Pack $pack */
                $pack = $this->collection->getNewEmptyItem();
                $pack->setData($data);
                $data = $this->convertProductsData($pack->getData());
                $result[$pack->getId()] = $data;
                $this->dataPersistor->clear(Pack::PERSISTENT_NAME);
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getCurrentPack()
    {
        return $this->coreRegistry->registry(Edit::CURRENT_PACK);
    }

    /**
     * @return mixed
     */
    private function getSavedPack()
    {
        return $this->dataPersistor->get(Pack::PERSISTENT_NAME);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function convertProductsData($data)
    {
        if (isset($data['product_ids']) && $data['product_ids']) {
            $sortedData = explode(',', $data['product_ids']);
            $productsData = $this->getProductsData($sortedData);
            /* save correct sort*/
            foreach ($sortedData as $key => $productId) {
                if (isset($productsData[$productId])) {
                    $sortedData[$key] = $productsData[$productId];
                } else {
                    unset($sortedData[$key]);
                }
            }

            $data['product_ids'] = [
                'child_products_container' => $sortedData
            ];
        }

        if (isset($data['parent_ids']) && $data['parent_ids']) {
            $data['parent_ids'] = [
                'parent_products_container' => array_values($this->getProductsData($data['parent_ids']))
            ];
        }

        return $data;
    }

    /**
     * @param array|string $productIds
     *
     * @return array
     */
    private function getProductsData($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter($productIds)
            ->addAttributeToSelect(['status', 'thumbnail', 'name', 'price'], 'left');

        $result = [];
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($productCollection->getItems() as $product) {
            $result[$product->getId()] = $this->fillData($product);
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    private function fillData(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return [
            'entity_id' => $product->getId(),
            'thumbnail' => $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl(),
            'name'      => $product->getName(),
            'status'    => $product->getStatus(),
            'type_id'   => $product->getTypeId(),
            'sku'       => $product->getSku(),
            'price'     => $product->getPrice() ? $this->priceModifier->toDefaultCurrency($product->getPrice()) : '-'
        ];
    }
}
