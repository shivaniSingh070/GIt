<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Api\Data;

interface PackProductInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const PACK_ID = 'pack_id';
    const STORE_ID = 'store_id';
    const PRODUCT_ID = 'product_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\Mostviewed\Api\Data\PackProductInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getPackId();

    /**
     * @param int $packId
     *
     * @return \Amasty\Mostviewed\Api\Data\PackProductInterface
     */
    public function setPackId($packId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\Mostviewed\Api\Data\PackProductInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     *
     * @return \Amasty\Mostviewed\Api\Data\PackProductInterface
     */
    public function setProductId($productId);
}
