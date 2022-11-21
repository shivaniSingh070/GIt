<?php

namespace Amasty\ImportCore\Api\Filter;

use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface;

interface FilterMetaInterface
{
    public function getJsConfig(FieldInterface $field): array;

    public function getConditions(FieldInterface $field): array;

    public function prepareConfig(FieldFilterInterface $filter, $value): FilterMetaInterface;

    public function getValue(FieldFilterInterface $filter);
}
