<?php

namespace Amasty\ImportCore\Api\Filter;

use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface;

interface FilterInterface
{
    public function filter(array $row, string $fieldName, FieldFilterInterface $filter): bool;
}
