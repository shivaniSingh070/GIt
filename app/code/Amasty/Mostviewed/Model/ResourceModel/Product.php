<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Eav\Model\Entity\Attribute\UniqueValidationInterface;

/**
 * Class Product
 * @package Amasty\Mostviewed\Model\ResourceModel
 */
class Product extends \Magento\Catalog\Model\ResourceModel\Product
{
    const QUERY_LIMIT = 1000;

    /**
     * @param int $productId
     * @param int $storeId
     * @param int $period
     * @return array
     */
    public function getProductViewesData($productId, $storeId, $period)
    {
        $tableName = $this->getTable('report_viewed_product_index');
        //TODO code refactoring - move select to resource model
        $connection = $this->getConnection();
        //get visitors who viewed this product
        $visitors = $connection->select()->from(['t2' => $tableName], ['visitor_id'])
            ->where('product_id = ?', $productId)
            ->where('visitor_id IS NOT NULL')
            ->where('store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(added_at) <= ?', $period)
            ->limit(self::QUERY_LIMIT);

        //get customers who viewed this product
        $customers = $connection->select()->from(['t2' => $tableName], ['customer_id'])
            ->where('product_id = ?', $productId)
            ->where('customer_id IS NOT NULL')
            ->where('store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(added_at) <= ?', $period)
            ->limit(self::QUERY_LIMIT);

        $visitors = array_unique($connection->fetchCol($visitors));
        $customers = array_unique($connection->fetchCol($customers));
        $customers = array_diff($customers, $visitors);

        // get related products
        $fields = [
            'id'  => 't.product_id',
            'cnt' => new \Zend_Db_Expr('COUNT(*)'),
        ];
        $productsByVisitor = [];
        if (!empty($visitors)) {
            $productsByVisitor = $connection->select()->from(['t' => $tableName], $fields)
                ->where('t.visitor_id IN (?)', $visitors)
                ->where('t.product_id != ?', $productId)
                ->where('store_id = ?', $storeId)
                ->group('t.product_id')
                ->order('cnt DESC')
                ->limit(self::QUERY_LIMIT);
            $productsByVisitor = $connection->fetchAll($productsByVisitor);
        }

        $productsByCustomer = [];
        if (!empty($customers)) {
            $productsByCustomer = $connection->select()->from(['t' => $tableName], $fields)
                ->where('t.customer_id IN (?)', $customers)
                ->where('t.product_id != ?', $productId)
                ->where('store_id = ?', $storeId)
                ->group('t.product_id')
                ->order('cnt DESC')
                ->limit(self::QUERY_LIMIT);
            $productsByCustomer = $connection->fetchAll($productsByCustomer);
        }
        return array_merge($productsByVisitor, $productsByCustomer);
    }

    /**
     * @param array $productIds
     * @param int $storeId
     * @param int $period
     * @param int $orderStatus
     * @return array
     */
    public function getBoughtTogetherProductData(array $productIds, $storeId, $period, $orderStatus)
    {
        $tableName = $this->getTable('sales_order_item');
        $connection = $this->getConnection();
        $productIdField = new \Zend_Db_Expr(
            'IF(configurable.parent_id IS NOT NULL, configurable.parent_id,'
            . ' IF(bundle.parent_product_id IS NOT NULL, bundle.parent_product_id, order_item.product_id))'
        );

        $orderSelect = $connection->select()
            ->from(['order_item' => $tableName], ['order_item.order_id'])
            ->where('order_item.product_id IN(?)', $productIds)
            ->where('order_item.store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(order_item.created_at) <= ?', $period);

        $productSelect = $connection->select()->from(
            ['order_item' => $tableName],
            ['id' => $productIdField, 'cnt' => new \Zend_Db_Expr('COUNT(*)')]
        )
            ->join(
                ['order' => $this->getTable('sales_order')],
                'order_item.order_id = order.entity_id',
                []
            )
            ->joinLeft(
                ['configurable' => $this->getTable('catalog_product_super_link')],
                'order_item.product_id = configurable.product_id',
                []
            )
            ->joinLeft(
                ['bundle' => $this->getTable('catalog_product_bundle_selection')],
                'order_item.product_id = bundle.product_id',
                []
            )
            ->where('order_item.product_id NOT IN(?)', $productIds)
            ->where('order.entity_id IN(?)', $orderSelect)
            ->where('order_item.store_id = ?', $storeId)
            ->group('order_item.product_id')
            ->order('cnt DESC')
            ->limit(self::QUERY_LIMIT);

        $this->addOrderStatusFilter($productSelect, $orderStatus);
        return $connection->fetchAll($productSelect);
    }

    /**
     * @param \Magento\Framework\DB\Select $collection
     *
     * @return \Magento\Framework\DB\Select
     */
    private function addOrderStatusFilter($select, $orderStatus)
    {
        if ($orderStatus && $orderStatus !== '0') {
            $select->where('order.status = ?', $orderStatus);
        }

        return $select;
    }
}
