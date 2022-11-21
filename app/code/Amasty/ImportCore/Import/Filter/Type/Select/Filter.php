<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Filter\Type\Select;

use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ImportCore\Import\Filter\AbstractFilter;
use Amasty\ImportCore\Import\Filter\FilterDataInterface;

class Filter extends AbstractFilter
{
    const TYPE_ID = 'select';

    protected function getFilterConfig(FieldFilterInterface $filter)
    {
        return $filter->getExtensionAttributes()->getSelectFilter();
    }

    protected function prepareFilterData(FilterDataInterface $filterData)
    {
        if ($filterData->getFilterConfig()->getIsMultiselect()
            && in_array($filterData->getCondition(), ['finset', 'nfinset'])
            && !empty($filterData->getFilterValue())
        ) {
            $condition = [];
            foreach ($filterData->getFilterValue() as $item) {
                $condition[] = [$filterData->getCondition() => $item];
            }
        }
    }
}
