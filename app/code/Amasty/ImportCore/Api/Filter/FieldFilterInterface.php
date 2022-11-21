<?php

namespace Amasty\ImportCore\Api\Filter;

interface FieldFilterInterface
{
    public function apply(array $row, string $fieldName): bool;
}
