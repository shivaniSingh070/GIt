<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data;

use Ulmod\OrderImportExport\Model\Data\Mapper as DataMapper;
use Ulmod\OrderImportExport\Model\Data as ModelData;
use Ulmod\OrderImportExport\Model\Data\Formatter\Iterator;
use Magento\Framework\DataObject;

interface FormatterInterface
{
    /**
     * @return ModelData
     */
    public function getModelData();

    /**
     * @param DataMapper $mapper
     * @return $this
     */
    public function setCustomFieldMapper(
        DataMapper $mapper = null
    );

    /**
     * @return null|DataMapper
     */
    public function getSystemFieldMapper();

    /**
     * @return null|DataMapper
     */
    public function getCustomFieldMapper();

    /**
     * @param Iterator[] $iterators
     * @return $this
     */
    public function setIterators(array $iterators);

    /**
     * @return Formatter\Iterator[]
     */
    public function getIterators();

    /**
     * @param string $field
     * @param Iterator $iterator
     * @return $this
     */
    public function addIterator(
        $field,
        Iterator $iterator
    );

    /**
     * @param string $field
     * @return Iterator|null
     */
    public function getIterator($field);

    /**
     * @return string
     */
    public function getFormat();

    /**
     * @param string $format
     * @return $this
     */
    public function setFormat($format);

    /**
     * @param null|string $value
     * @return $this
     */
    public function setPrepend($value);

    /**
     * @return null|string
     */
    public function getPrepend();

    /**
     * @param string $value
     * @return $this
     */
    public function setGlue($value);

    /**
     * @return string
     */
    public function getGlue();
    
    /**
     * @param null|string $value
     * @return $this
     */
    public function setAppend($value);

    /**
     * @return null|string
     */
    public function getAppend();

    /**
     * @param array $fields
     * @return $this
     */
    public function setIncludedFields(array $fields);

    /**
     * @return array
     */
    public function getIncludedFields();

    /**
     * @param string $pattern
     * @return $this
     */
    public function setValueWrapPattern($pattern);

    /**
     * @return string
     */
    public function getValueWrapPattern();

    /**
     * @param array $fields
     * @return $this
     */
    public function setExcludedFields(array $fields);

    /**
     * @return array
     */
    public function getExcludedFields();

    /**
     * @param array|DataObject $item
     * @return string|array|DataObject
     */
    public function format($item);

    /**
     * @param array $values
     * @return $this
     */
    public function setDefaultValues(array $values);

    /**
     * @return array
     */
    public function getDefaultValues();
}
