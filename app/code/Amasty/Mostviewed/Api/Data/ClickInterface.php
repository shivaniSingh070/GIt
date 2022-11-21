<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Api\Data;

interface ClickInterface
{
    const MAIN_TABLE = 'mostviewed_click_temp';
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const VISITOR_ID = 'visitor_id';
    const PRODUCT_ID = 'product_id';
    const BLOCK_ID = 'block_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getVisitorId();

    /**
     * @param string $visitorId
     *
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     */
    public function setVisitorId($visitorId);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     *
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     */
    public function setProductId($productId);

    /**
     * @return int
     */
    public function getBlockId();

    /**
     * @param int $blockId
     *
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     */
    public function setBlockId($blockId);
}
