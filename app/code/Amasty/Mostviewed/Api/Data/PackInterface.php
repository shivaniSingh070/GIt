<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Api\Data;

interface PackInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const PACK_ID = 'pack_id';
    const STORE_ID = 'store_id';
    const STATUS = 'status';
    const PRIORITY = 'priority';
    const NAME = 'name';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    const PRODUCT_IDS = 'product_ids';
    const BLOCK_TITLE = 'block_title';
    const DISCOUNT_TYPE = 'discount_type';
    const APPLY_FOR_PARENT = 'apply_for_parent';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const CREATED_AT = 'created_at';
    const CART_MESSAGE = 'cart_message';
    const DATE_FROM = 'date_from';
    const DATE_TO = 'date_to';
    /**#@-*/

    /**
     * @return int
     */
    public function getPackId();

    /**
     * @param int $packId
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setPackId($packId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setPriority($priority);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getCustomerGroupIds();

    /**
     * @param string $customerGroupIds
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setCustomerGroupIds($customerGroupIds);

    /**
     * @return string
     */
    public function getProductIds();

    /**
     * @param string $productIds
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setProductIds($productIds);

    /**
     * @return string|null
     */
    public function getBlockTitle();

    /**
     * @param string|null $blockTitle
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setBlockTitle($blockTitle);

    /**
     * @return int
     */
    public function getDiscountType();

    /**
     * @param int $discountType
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setDiscountType($discountType);

    /**
     * @return int
     */
    public function getApplyForParent();

    /**
     * @param int $applyForParent
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setApplyForParent($applyForParent);

    /**
     * @return string|null
     */
    public function getDiscountAmount();

    /**
     * @param string|null $discountAmount
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setDiscountAmount($discountAmount);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string|null $createdAt
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getDateFrom();

    /**
     * @param string|null $dateFrom
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setDateFrom($dateFrom);

    /**
     * @return string|null
     */
    public function getDateTo();

    /**
     * @param string|null $dateTo
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setDateTo($dateTo);

    /**
     * @return string|null
     */
    public function getCartMessage();

    /**
     * @param string|null $cartMessage
     *
     * @return \Amasty\Mostviewed\Api\Data\PackInterface
     */
    public function setCartMessage($cartMessage);
}
