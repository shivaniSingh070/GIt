<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Analytics;

use Magento\Framework\Model\AbstractModel;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Click as ClickResource;
use Amasty\Mostviewed\Api\Data\ClickInterface;

/**
 * Class Click
 * @package Amasty\Mostviewed\Model\Analytics
 */
class Click extends AbstractModel implements ClickInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ClickResource::class);
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->getData(ClickInterface::VISITOR_ID);
    }

    /**
     * @param string $visitorId
     *
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     */
    public function setVisitorId($visitorId)
    {
        return $this->setData(ClickInterface::VISITOR_ID, $visitorId);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(ClickInterface::PRODUCT_ID);
    }

    /**
     * @param int $productId
     *
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(ClickInterface::PRODUCT_ID, $productId);
    }

    /**
     * @return int
     */
    public function getBlockId()
    {
        return $this->getData(ClickInterface::BLOCK_ID);
    }

    /**
     * @param int $blockId
     *
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     */
    public function setBlockId($blockId)
    {
        return $this->setData(ClickInterface::BLOCK_ID, $blockId);
    }
}
