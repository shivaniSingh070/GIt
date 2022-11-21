<?php

namespace Amasty\ExportCore\Api\Filter;

use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Magento\Framework\Data\Collection;

interface FilterInterface
{
    public function apply(Collection $collection, FieldFilterInterface $filter);
}
