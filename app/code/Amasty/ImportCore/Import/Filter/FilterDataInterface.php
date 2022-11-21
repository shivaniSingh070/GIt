<?php

namespace Amasty\ImportCore\Import\Filter;

interface FilterDataInterface
{
    /**
     * @return array|string
     */
    public function getCondition();

    /**
     * @param array|string $condition
     *
     * @return \Amasty\ImportCore\Import\Filter\FilterDataInterface
     */
    public function setCondition($condition): FilterDataInterface;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     *
     * @return \Amasty\ImportCore\Import\Filter\FilterDataInterface
     */
    public function setValue($value): FilterDataInterface;

    /**
     * @return mixed
     */
    public function getFilterValue();

    /**
     * @param mixed $filterValue
     *
     * @return \Amasty\ImportCore\Import\Filter\FilterDataInterface
     */
    public function setFilterValue($filterValue): FilterDataInterface;

    /**
     * @return mixed
     */
    public function getFilterConfig();

    /**
     * @param mixed $filterConfig
     *
     * @return \Amasty\ImportCore\Import\Filter\FilterDataInterface
     */
    public function setFilterConfig($filterConfig): FilterDataInterface;
}
