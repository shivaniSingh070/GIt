<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Ulmod\OrderImportExport\Model\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductModel;

class Product
{
    /**
     * @var ProductModel
     */
    private $resource;

    /**
     * @param ProductModel $resource
     */
    public function __construct(
        ProductModel $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @return array
     */
    public function getSkuEntityIdPairs()
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getEntityTable(),
            [ProductInterface::SKU, 'entity_id']
        );

        return $connection->fetchPairs($select);
    }

    /**
     * @return array
     */
    public function getEntityIdSkuPairs()
    {
        $connection = $this->resource->getConnection();
        $select  = $connection->select()->from(
            $this->resource->getEntityTable(),
            ['entity_id', ProductInterface::SKU]
        );

        return $connection->fetchPairs($select);
    }
}
