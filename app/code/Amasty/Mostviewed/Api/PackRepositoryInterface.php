<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Api;

use Amasty\Mostviewed\Api\Data\PackInterface;

/**
 * @api
 */
interface PackRepositoryInterface
{
    /**
     * Save
     *
     * @param PackInterface $pack
     * @return PackInterface
     */
    public function save(PackInterface $pack);

    /**
     * Get by id
     *
     * @param int $packId
     * @return PackInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($packId);

    /**
     * Delete
     *
     * @param PackInterface $pack
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(PackInterface $pack);

    /**
     * Duplicate
     *
     * @param PackInterface $pack
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function duplicate(PackInterface $pack);

    /**
     * Delete by id
     *
     * @param int $packId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($packId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get pack list by params
     *
     * @param array $productIds
     * @param int $storeId
     *
     * @return bool|\Magento\Framework\Api\ExtensibleDataInterface[]|\Magento\Framework\Api\SearchResultsInterface|\Magento\Ui\Api\Data\BookmarkInterface[]
     */
    public function getPacksByParentProductsAndStore($productIds, $storeId);

    /**
     * Get pack list by params
     *
     * @param array $productIds
     * @param int $storeId
     *
     * @return bool|\Magento\Framework\Api\ExtensibleDataInterface[]|\Magento\Framework\Api\SearchResultsInterface|\Magento\Ui\Api\Data\BookmarkInterface[]
     */
    public function getPacksByChildProductsAndStore($productIds, $storeId);
}
