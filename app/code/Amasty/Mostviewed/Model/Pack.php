<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model;

use \Amasty\Mostviewed\Api\Data\PackInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Pack
 * @package Amasty\Mostviewed\Model
 */
class Pack extends \Magento\Framework\Model\AbstractModel implements PackInterface, IdentityInterface
{
    const PERSISTENT_NAME = 'amasty_mostviewed_pack';

    const CACHE_TAG = 'mostviewed_pack';

    protected function _construct()
    {
        $this->_init(\Amasty\Mostviewed\Model\ResourceModel\Pack::class);
        $this->setIdFieldName('pack_id');
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getPackId()];
    }

    /**
     * @inheritdoc
     */
    public function getPackId()
    {
        return $this->_getData(PackInterface::PACK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPackId($packId)
    {
        $this->setData(PackInterface::PACK_ID, $packId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(PackInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(PackInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(PackInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(PackInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->_getData(PackInterface::PRIORITY);
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        $this->setData(PackInterface::PRIORITY, $priority);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(PackInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(PackInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupIds()
    {
        return $this->_getData(PackInterface::CUSTOMER_GROUP_IDS);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupIds($customerGroupIds)
    {
        $this->setData(PackInterface::CUSTOMER_GROUP_IDS, $customerGroupIds);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductIds()
    {
        return $this->_getData(PackInterface::PRODUCT_IDS);
    }

    /**
     * @inheritdoc
     */
    public function setProductIds($productIds)
    {
        $this->setData(PackInterface::PRODUCT_IDS, $productIds);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBlockTitle()
    {
        return $this->_getData(PackInterface::BLOCK_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setBlockTitle($blockTitle)
    {
        $this->setData(PackInterface::BLOCK_TITLE, $blockTitle);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountType()
    {
        return $this->_getData(PackInterface::DISCOUNT_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountType($discountType)
    {
        $this->setData(PackInterface::DISCOUNT_TYPE, $discountType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getApplyForParent()
    {
        return $this->_getData(PackInterface::APPLY_FOR_PARENT);
    }

    /**
     * @inheritdoc
     */
    public function setApplyForParent($applyForParent)
    {
        $this->setData(PackInterface::APPLY_FOR_PARENT, $applyForParent);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountAmount()
    {
        return $this->_getData(PackInterface::DISCOUNT_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->setData(PackInterface::DISCOUNT_AMOUNT, $discountAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(PackInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(PackInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateFrom()
    {
        return $this->_getData(PackInterface::DATE_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setDateFrom($dateFrom)
    {
        $this->setData(PackInterface::DATE_FROM, $dateFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateTo()
    {
        return $this->_getData(PackInterface::DATE_TO);
    }

    /**
     * @inheritdoc
     */
    public function setDateTo($dateTo)
    {
        $this->setData(PackInterface::DATE_TO, $dateTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCartMessage()
    {
        return $this->_getData(PackInterface::CART_MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setCartMessage($cartMessage)
    {
        $this->setData(PackInterface::CART_MESSAGE, $cartMessage);

        return $this;
    }
}
