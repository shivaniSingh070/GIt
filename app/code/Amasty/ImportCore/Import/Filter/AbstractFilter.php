<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Import\Filter;

use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ImportCore\Api\Filter\FilterInterface;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * @var FilterDataInterfaceFactory
     */
    protected $filterDataFactory;

    public function __construct(FilterDataInterfaceFactory $filterDataFactory)
    {
        $this->filterDataFactory = $filterDataFactory;
    }

    abstract protected function getFilterConfig(FieldFilterInterface $filter);

    abstract protected function prepareFilterData(FilterDataInterface $filterData);

    public function filter(array $row, string $fieldName, FieldFilterInterface $filter): bool
    {
        if (!($filterData = $this->getFilterData($row, $fieldName, $filter))) {
            return false;
        }

        $this->prepareFilterData($filterData);

        return $this->applyFilter($filterData);
    }

    private function getFilterData(
        array $row,
        string $fieldName,
        FieldFilterInterface $filter
    ): ?FilterDataInterface {
        $config = $this->getFilterConfig($filter);
        if (!$config || !isset($row[$fieldName])) {
            return null;
        }

        /** @var FilterDataInterface $filterData */
        $filterData = $this->filterDataFactory->create();
        $filterData->setValue($row[$fieldName])
            ->setFilterValue($config->getValue())
            ->setCondition($filter->getCondition())
            ->setFilterConfig($config);

        return $filterData;
    }

    private function applyFilter(FilterDataInterface $filterData): bool
    {
        return $this->processFilter(
            $filterData->getCondition(),
            $filterData->getValue(),
            $filterData->getFilterValue()
        );
    }

    private function processFilter($condition, $value, $filterValue): bool
    {
        if (is_array($condition)) {
            $result = true;
            foreach ($condition as $conditionName => $conditionValue) {
                if (is_int($conditionName) && is_array($conditionValue)) {
                    $result = false;
                    if ($this->processFilter($conditionValue, $value, null)) { // Logical OR
                        return true;
                    }
                } elseif (!$this->processFilter($conditionName, $value, $conditionValue)) { // Logical AND
                    return false;
                }
            }

        } else {
            switch ($condition) {
                case 'eq':
                    $result = $value == $filterValue;
                    break;
                case 'neq':
                    $result = $value != $filterValue;
                    break;
                case 'like':
                    $result = mb_stripos((string)$value, (string)$filterValue) !== false;
                    break;
                case 'nlike':
                    $result = mb_stripos((string)$value, (string)$filterValue) === false;
                    break;
                case 'in':
                    $result = in_array($value, (array)$filterValue);
                    break;
                case 'nin':
                    $result = !in_array($value, (array)$filterValue);
                    break;
                case 'notnull':
                    $result = $value !== null;
                    break;
                case 'null':
                    $result = $value === null;
                    break;
                case 'gt':
                    $result = $value > $filterValue;
                    break;
                case 'lt':
                    $result = $value < $filterValue;
                    break;
                case 'gteq':
                    $result = $value >= $filterValue;
                    break;
                case 'lteq':
                    $result = $value <= $filterValue;
                    break;
                case 'finset':
                    $result = in_array($filterValue, explode(',', (string)$value));
                    break;
                case 'nfinset':
                    $result = !in_array($filterValue, explode(',', (string)$value));
                    break;
                default:
                    $result = false;
            }

        }

        return $result;
    }
}
