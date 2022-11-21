<?php

namespace Amasty\ImportCore\Import\Filter;

class FilterData implements FilterDataInterface
{
    /**
     * @var array|string
     */
    private $condition;

    private $value;

    private $filterValue;

    private $filterConfig;

    public function getCondition()
    {
        return $this->condition;
    }

    public function setCondition($condition): FilterDataInterface
    {
        $this->condition = $condition;

        return $this;
    }
    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): FilterDataInterface
    {
        $this->value = $value;

        return $this;
    }

    public function getFilterValue()
    {
        return $this->filterValue;
    }

    public function setFilterValue($filterValue): FilterDataInterface
    {
        $this->filterValue = $filterValue;

        return $this;
    }

    public function getFilterConfig()
    {
        return $this->filterConfig;
    }

    public function setFilterConfig($filterConfig): FilterDataInterface
    {
        $this->filterConfig = $filterConfig;

        return $this;
    }
}
