<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel\Product;

/**
 * Class Collection
 * @package Amasty\Mostviewed\Model\ResourceModel\Product
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @return $this
     */
    public function updateTotalRecords()
    {
        $this->_totalRecords = count($this->getItems());

        return $this;
    }

    /**
     * @param array $items
     *
     * @return $this
     */
    public function setItems($items)
    {
        $this->_items = $items;

        return $this;
    }

    /**
     * @param array $bundleIds
     * @param int $storeId
     *
     * @return $this
     */
    public function applyBundleFilter($bundleIds, $storeId)
    {
        $this->getSelect()->join(
            ['pack' => $this->getTable(\Amasty\Mostviewed\Model\ResourceModel\Pack::PACK_PRODUCT_TABLE)],
            'e.entity_id = pack.product_id',
            ['bundle_pack_id' => 'pack.pack_id']
        )
            ->where('pack.pack_id IN(?)', $bundleIds)
            ->where('pack.store_id IN(?)', [0, $storeId]);

        return $this;
    }

    /**
     * Get SQL for get record count without left JOINs
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->reset(\Magento\Framework\DB\Select::GROUP);
        return $select;
    }
}
