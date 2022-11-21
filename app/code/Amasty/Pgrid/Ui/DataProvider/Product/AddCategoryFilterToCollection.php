<?php

declare(strict_types=1);

namespace Amasty\Pgrid\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Framework\App\ResourceConnection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class AddCategoryFilterToCollection implements AddFilterToCollectionInterface
{
    const NO_CATEGORY_FILTER = 'no_category';

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if ($this->isNoCategoryCondition($condition)) {
            $categoryTableName = 'amasty_category';
            $from = $collection->getSelect()->getPart('from');
            if (!isset($from[$categoryTableName])) {
                $collection->getSelect()->joinLeft(
                    [$categoryTableName => $this->resource->getTableName('catalog_category_product')],
                    'e.entity_id=amasty_category.product_id',
                    ['category_id']
                );

                $collection->getSelect()->where('amasty_category.category_id IS NULL');
            }
        } else {
            $collection->addCategoriesFilter($condition);
        }
    }

    private function isNoCategoryCondition($condition = null): bool
    {
        return isset($condition['in'])
            && reset($condition['in']) === self::NO_CATEGORY_FILTER;
    }
}
