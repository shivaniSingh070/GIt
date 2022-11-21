<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Filtering;

use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface as EntityFieldFilterInterface;
use Amasty\ImportCore\Api\Filter\FieldFilterInterface;
use Amasty\ImportCore\Api\Filter\FilterInterface;

class FieldFilter implements FieldFilterInterface
{
    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @var EntityFieldFilterInterface
     */
    private $entityFilter;

    public function __construct(
        FilterInterface $filter,
        EntityFieldFilterInterface $entityFilter
    ) {
        $this->filter = $filter;
        $this->entityFilter = $entityFilter;
    }

    public function apply(array $row, string $fieldName): bool
    {
        return $this->filter->filter($row, $fieldName, $this->entityFilter);
    }
}
