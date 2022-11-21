<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Filter\Type\Toggle;

use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ImportCore\Import\Filter\AbstractFilter;
use Amasty\ImportCore\Import\Filter\FilterDataInterface;

class Filter extends AbstractFilter
{
    const TYPE_ID = 'toggle';

    protected function getFilterConfig(FieldFilterInterface $filter)
    {
        return $filter->getExtensionAttributes()->getToggleFilter();
    }

    protected function prepareFilterData(FilterDataInterface $filterData)
    {
        $filterData->setValue((bool)$filterData->getValue());
        $filterData->setFilterValue((bool)$filterData->getFilterValue());
    }
}
