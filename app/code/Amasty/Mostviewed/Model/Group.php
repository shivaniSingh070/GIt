<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model;

use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Class Group
 * @package Amasty\Mostviewed\Model
 * @codingStandardsIgnoreFile
 */
class Group extends AbstractGroup implements GroupInterface
{
    const CACHE_TAG = 'mostviewed_group';

    const FORM_NAME = 'amasty_mostviewed_product_group_form';

    const PERSISTENT_NAME = 'amasty_mostviewed_rule';

    /**
     * Store rule combine conditions model
     *
     * @var \Magento\Rule\Model\Condition\Combine|null
     */
    private $whereConditions;

    /**
     * Store rule combine conditions model
     *
     * @var \Magento\Rule\Model\Condition\Combine|null
     */
    private $sameAsConditions;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    private $combineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    private $whereCombineFactory;

    /**
     * @var \Amasty\Mostviewed\Model\Rule\Condition\SameAsCombineFactory
     */
    private $sameAsCombineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    private $actionFactory;

    /**
     * @var array
     */
    private $matchedProducts = [];

    /**
     * @var \Magento\Rule\Model\Condition\Combine
     */
    private $currentConditions;

    /**
     * @var \Amasty\Mostviewed\Model\Indexer\RuleProcessor
     */
    private $ruleProcessor;

    /**
     * @var Layout\Updater
     */
    private $layoutUpdater;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    private $stockResource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Mostviewed\Model\ResourceModel\Group::class);
        $this->setIdFieldName('group_id');
        $this->combineFactory = $this->getData('combineFactory');
        $this->whereCombineFactory = $this->getData('whereCombineFactory');
        $this->sameAsCombineFactory = $this->getData('sameAsCombineFactory');
        $this->actionFactory = $this->getData('actionFactory');
        $this->ruleProcessor = $this->getData('ruleProcessor');
        $this->layoutUpdater = $this->getData('layoutUpdater');
        $this->stockResource = $this->getData('stockResource');
        $this->moduleManager = $this->getData('moduleManager');
        if ($this->getData('amastySerializer')) {
            $this->serializer = $this->getData('amastySerializer');
        }
    }

    /**
     * Getter for rule conditions collection. Product Conditions
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule conditions collection. Product Conditions
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getWhereConditionsInstance()
    {
        return $this->whereCombineFactory->create();
    }

    /**
     * Getter for rule conditions collection. Product Conditions
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getSameAsConditionsInstance()
    {
        return $this->sameAsCombineFactory->create();
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getWhereConditions()
    {
        if (empty($this->whereConditions)) {
            $this->_resetWhereConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasWhereConditionsSerialized()) {
            $conditions = $this->getWhereConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->whereConditions->loadArray($conditions);
                }
            }
            $this->unsWhereConditionsSerialized();
        }

        return $this->whereConditions;
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getSameAsConditions()
    {
        if (empty($this->sameAsConditions)) {
            $this->_resetSameAsConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasSameAsConditionsSerialized()) {
            $conditions = $this->getSameAsConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->sameAsConditions->loadArray($conditions);
                }
            }
            $this->unsSameAsConditionsSerialized();
        }

        return $this->sameAsConditions;
    }

    /**
     * Reset rule combine conditions
     *
     * @param null|\Magento\Rule\Model\Condition\Combine $conditions
     *
     * @return $this
     */
    protected function _resetWhereConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getWhereConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setWhereConditions($conditions);

        return $this;
    }

    /**
     * Reset rule combine conditions
     *
     * @param null|\Magento\Rule\Model\Condition\Combine $conditions
     *
     * @return $this
     */
    protected function _resetSameAsConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getSameAsConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setSameAsConditions($conditions);

        return $this;
    }

    /**
     * Set rule combine conditions model
     *
     * @param \Magento\Rule\Model\Condition\Combine $conditions
     *
     * @return $this
     */
    public function setWhereConditions($conditions)
    {
        $this->whereConditions = $conditions;

        return $this;
    }

    /**
     * Set rule combine conditions model
     *
     * @param \Magento\Rule\Model\Condition\Combine $conditions
     *
     * @return $this
     */
    public function setSameAsConditions($conditions)
    {
        $this->sameAsConditions = $conditions;

        return $this;
    }

    /**
     * Initialize rule model data from array
     *
     * @param array $data
     *
     * @return $this
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['where_conditions'])) {
            $this->getWhereConditions()
                ->setWhereConditions([])
                ->loadArray($arr['where_conditions'][1], 'where_conditions');
        }
        if (isset($arr['same_as_conditions'])) {
            $this->getSameAsConditions()
                ->setSameAsConditions([])
                ->loadArray($arr['same_as_conditions'][1], 'same_as_conditions');
        }

        return $this;
    }

    /**
     * Set specified data to current rule.
     * Set conditions and actions recursively.
     * Convert dates into \DateTime.
     *
     * @param array $data
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertFlatToRecursive(array $data)
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['conditions', 'where_conditions', 'same_as_conditions']) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = &$arr;
                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = &$node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * Convert dates into \DateTime
                 */
                if (in_array($key, ['from_date', 'to_date'], true) && $value) {
                    $value = new \DateTime($value);
                }
                $this->setData($key, $value);
            }
        }

        return $arr;
    }

    /**
     * @return $this|AbstractGroup
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();

        // Serialize conditions
        $where = $this->getWhereConditions();
        if ($where) {
            $this->setWhereConditionsSerialized($this->serializer->serialize($where->asArray()));
            $this->whereConditions = null;
        }

        // Serialize conditions
        $sameAs = $this->getSameAsConditions();
        if ($sameAs) {
            $this->setSameAsConditionsSerialized($this->serializer->serialize($sameAs->asArray()));
            $this->sameAsConditions = null;
        }

        if ($this->hasStores()) {
            $storeIds = $this->getStores();
            if (is_array($storeIds) && !empty($storeIds)) {
                $this->setStores(implode(',', $storeIds));
            }
        }

        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        if ($this->hasCategoryIds()) {
            $categoryIds = $this->getCategoryIds();
            if (is_array($categoryIds) && !empty($categoryIds)) {
                $this->setCategoryIds(implode(',', $categoryIds));
            }
        }

        if (!$this->getGroupId()) {
            $this->setGroupId(null);
        }

        if ($this->shouldBeAddedToLayout()) {
            $this->layoutUpdater->execute(
                $this
            );
        } else {
            $this->layoutUpdater->delete($this->getLayoutUpdateId());
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function shouldBeAddedToLayout()
    {
        return !in_array(
            $this->getBlockPosition(),
            [
                BlockPosition::PRODUCT_INTO_UPSELL,
                BlockPosition::CART_INTO_CROSSSEL,
                BlockPosition::PRODUCT_INTO_RELATED,
                BlockPosition::CUSTOM
            ]
        );
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getMatchingProductIdsByGroup()
    {
        $relation = $this->getRelation();
        if (!isset($this->matchedProducts[$relation])) {
            $this->matchedProducts[$relation] = [];
            $this->setCollectedAttributes([]);
            $stores = explode(',', $this->getStores());
            if (in_array(0, $stores)) {
                $allStores = $this->_storeManager->getStores();
                $stores = [];
                foreach ($allStores as $store) {
                    $stores[] = $store->getId();
                }
            }
            foreach ($stores as $storeId) {
                switch ($relation) {
                    case RuleIndex::WHAT_SHOW:
                        $this->currentConditions = $this->getConditions();
                        if (!$this->currentConditions->getConditions()) {
                            $this->currentConditions = null; // all products- don't apply
                        }
                        break;
                    case RuleIndex::WHERE_SHOW:
                        if ($this->getCategoryIds()) {
                            foreach (explode(',', $this->getCategoryIds()) as $categoryId) {
                                $this->matchedProducts[$relation]['categories'][$categoryId][] = $storeId;
                            }
                        } else {
                            $this->currentConditions = $this->getWhereConditions();
                        }
                        break;
                }
                if ($this->currentConditions) {
                    $this->collectProductsByConditions(
                        $storeId,
                        $relation
                    );
                } else {
                    if (!$this->getCategoryIds()) {
                        $this->matchedProducts[$relation] = null;
                    }
                }
            }
        }

        return $this->matchedProducts[$relation];
    }

    /**
     * @param Collection $collection
     * @param Product $product
     */
    public function applySameAsConditions(Collection $collection, Product $product)
    {
        $conditions = [];
        $combineConditions = $this->getSameAsConditions();
        if ($combineConditions && is_array($combineConditions->getData('same_as_conditions'))) {
            $conditions = $combineConditions->getData('same_as_conditions');
        }
        if (!empty($conditions)) {
            $appliedCondition = 0;
            foreach ($conditions as $sameAsCondition) {
                if (method_exists($sameAsCondition, 'apply')) {
                    if ($sameAsCondition->apply($collection, $product, $combineConditions->getValue())) {
                        $appliedCondition++;
                    }
                }
            }
            if ($combineConditions->getAggregator() == 'any' && $appliedCondition) {
                $this->changeAggregator($collection, $appliedCondition);
            }
        }
    }

    /**
     * Change AND operator to OR
     *
     * @param $collection
     * @param $appliedConditions
     */
    private function changeAggregator($collection, $appliedConditions)
    {
        $where = $collection->getSelect()->getPart(\Zend_Db_Select::WHERE);
        $sameConditions = array_slice($where, -1 * $appliedConditions, null, true);
        $sameWhere = '';
        $andRegexp = '@' . \Zend_Db_Select::SQL_AND . '@';
        foreach ($sameConditions as $key => $sameCondition) {
            if (empty($sameWhere)) {
                if (count($sameConditions) != count($where)) {
                    $sameWhere .= ' ' . \Zend_Db_Select::SQL_AND;
                }
                $sameWhere .= ' (' .
                    preg_replace($andRegexp, '', $sameCondition, 1);
            } else {
                $sameWhere .= ' ' . preg_replace($andRegexp, \Zend_Db_Select::SQL_OR, $sameCondition, 1);
            }
            unset($where[$key]);
        }
        $sameWhere .= ')';
        $where[] = $sameWhere;
        $collection->getSelect()->setPart(\Zend_Db_Select::WHERE, $where);
    }

    /**
     * @param $storeId
     * @param $relation
     */
    private function collectProductsByConditions($storeId, $relation)
    {
        /** @var $productCollection Collection */
        $productCollection = $this->_productCollectionFactory->create()
            ->setStoreId($storeId);

        if ($this->_productsFilter) {
            $productCollection->addIdFilter($this->_productsFilter);
        }
        if ($relation == RuleIndex::WHERE_SHOW && $this->getShowForOutOfStock()) {
            $this->stockResource->addStockDataToCollection($productCollection, false);
            $fromTables = $productCollection->getSelect()->getPart('from');
            if ($this->moduleManager->isEnabled('Magento_Inventory') &&
                $fromTables['stock_status_index']['tableName'] !=
                $productCollection->getResource()->getTable('cataloginventory_stock_status')
            ) {
                $salableColumn = 'is_salable';
            } else {
                $salableColumn = 'stock_status';
            }
            $productCollection->getSelect()->where('stock_status_index.' . $salableColumn . ' = 0');
        }

        $this->currentConditions->collectValidatedAttributes($productCollection);

        $this->_resourceIterator->walk(
            $productCollection->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                'attributes' => $this->getCollectedAttributes(),
                'product'    => $this->_productFactory->create(),
                'store_id'   => $storeId,
                'relation'   => $relation
            ]
        );
    }

    /**
     * @param array $args
     */
    public function callbackValidateProduct($args)
    {
        $product = $args['product'];
        $storeId = $args['store_id'];
        $relation = $args['relation'];

        $product->setData($args['row']);
        $product->setStoreId($storeId);

        if ($this->currentConditions->validate($product)) {
            $this->matchedProducts[$relation][$product->getId()][] = $storeId;
        }
    }

    /**
     * @param string $action
     * @param float|int|string $discount
     *
     * @return array
     */
    public function validateDiscount($action, $discount)
    {
        return [];
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if (!$this->ruleProcessor->isIndexerScheduled()) {
            $this->getResource()->addCommitCallback([$this, 'reindex']);
        }

        return $this;
    }

    /**
     *
     */
    public function reindex()
    {
        $this->ruleProcessor->reindexRow($this->getId());
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . '_product_group_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getWhereConditionsFieldSetId($formName = '')
    {
        return $formName . '_product_group_where_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getSameAsConditionsFieldSetId($formName = '')
    {
        return $formName . '_product_group_same_as_conditions_fieldset_' . $this->getId();
    }
}
