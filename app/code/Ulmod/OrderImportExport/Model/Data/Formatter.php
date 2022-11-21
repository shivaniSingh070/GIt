<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObjectFactory;
use Ulmod\OrderImportExport\Model\Data as ModelData;
use Ulmod\OrderImportExport\Model\Data\Mapper as SystemFieldMapper;
use Ulmod\OrderImportExport\Model\Data\Mapper as CustomFieldMapper;
use Ulmod\OrderImportExport\Model\Data\Formatter\Iterator;
use Magento\Framework\DataObject;
        
class Formatter implements FormatterInterface
{
    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var SystemFieldMapper
     */
    private $systemFieldMapper;

    /**
     * @var ModelData
     */
    private $modelData;

    /**
     * @var CustomFieldMapper
     */
    private $customFieldMapper;

    /**
     * @var string
     */
    private $format;

    /**
     * @var Iterator[]
     */
    private $iterators = [];

    /**
     * @var string
     */
    private $glue;

    /**
     * @var string|null
     */
    private $append;

    /**
     * @var string|null
     */
    private $prepend;

    /**
     * @var string
     */
    private $valueWrapPattern;

    /**
     * @var array
     */
    private $excludedFields;

    /**
     * @var array
     */
    private $includedFields;

    /**
     * @var array
     */
    private $defaultValues;

    /**
     * @var bool
     */
    private $allowReturnChar;

    /**
     * @var bool
     */
    private $allowNewlineChar;

    /**
     * @var bool
     */
    private $allowTabChar;

    public function __construct(
        DataObjectFactory $objectFactory,
        ModelData $modelData,
        SystemFieldMapper $systemFieldMapper,
        CustomFieldMapper $customFieldMapper,
        array $iterators = [],
        $format = 'string',
        $prepend = null,
        $glue = null,
        $append = null,
        $valueWrapPattern = null,
        array $excludedFields = [],
        array $includedFields = [],
        array $defaultValues = [],
        $allowNewlineChar = true,
        $allowTabChar = true,
        $allowReturnChar = true
    ) {
        $this->objectFactory = $objectFactory;
        $this->modelData = $modelData;
        $this->systemFieldMapper = $systemFieldMapper;
        $this->setAllowNewlineChar($allowNewlineChar);
        $this->setAllowTabChar($allowTabChar);
        $this->setAllowReturnChar($allowReturnChar);
        $this->setCustomFieldMapper($customFieldMapper);
        $this->setIterators($iterators);
        $this->setFormat($format);
        $this->setPrepend($prepend);
        $this->setGlue($glue);
        $this->setAppend($append);
        $this->setValueWrapPattern($valueWrapPattern);
        $this->setExcludedFields($excludedFields);
        $this->setIncludedFields($includedFields);
        $this->setDefaultValues($defaultValues);
    }

    /**
     * @return ModelData
     */
    public function getModelData()
    {
        return $this->modelData;
    }

    /**
     * @param CustomFieldMapper $mapper
     *
     * @return $this
     */
    public function setCustomFieldMapper(
        CustomFieldMapper $mapper = null
    ) {
        $this->customFieldMapper = $mapper;

        return $this;
    }

    /**
     * @return null|SystemFieldMapper
     */
    public function getSystemFieldMapper()
    {
        return $this->systemFieldMapper;
    }
    
    /**
     * @return null|CustomFieldMapper
     */
    public function getCustomFieldMapper()
    {
        return $this->customFieldMapper;
    }

    /**
     * @param string $field
     * @param Iterator $iterator
     *
     * @return $this
     */
    public function addIterator($field, Iterator $iterator)
    {
        $this->iterators[$field] = $iterator;

        return $this;
    }

    /**
     * @param Iterator[] $iterators
     *
     * @return $this
     */
    public function setIterators(array $iterators)
    {
        foreach ($iterators as $field => $iterator) {
            $this->addIterator($field, $iterator);
        }

        return $this;
    }

    /**
     * @param string $field
     * @return Iterator|null
     */
    public function getIterator($field)
    {
        if (isset($this->iterators[$field])) {
            return $this->iterators[$field];
        }

        return null;
    }
    
    /**
     * @return Iterator[]
     */
    public function getIterators()
    {
        return $this->iterators;
    }

    /**
     * @param array|DataObject $item
     */
    private function executeIterators(&$item)
    {
        foreach ($this->getIterators() as $field => $iterator) {
            if (is_array($item) && isset($item[$field])) {
                $item[$field] = $iterator->iterate($item[$field]);
            } elseif ($item instanceof DataObject) {
                $fieldData = $item->getData($field);
                $item->setData(
                    $field,
                    $iterator->iterate($fieldData)
                );
            }
        }
    }

    /**
     * @return bool
     */
    public function getAllowNewlineChar()
    {
        return $this->allowNewlineChar;
    }
    
    /**
     * @param bool $bool
     * @return $this
     */
    public function setAllowNewlineChar($bool)
    {
        $this->allowNewlineChar = (bool)$bool;

        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function setAllowReturnChar($bool)
    {
        $this->allowReturnChar = (bool)$bool;

        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function setAllowTabChar($bool)
    {
        $this->allowTabChar = (bool)$bool;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowReturnChar()
    {
        return $this->allowReturnChar;
    }

    /**
     * @return bool
     */
    public function getAllowTabChar()
    {
        return $this->allowTabChar;
    }

    /**
     * @return string
     */
    public function getGlue()
    {
        return $this->glue;
    }

    /**
     * @param string|array $value
     * @return $this
     */
    public function setGlue($value)
    {
        $this->glue = $this->prepareGlue($value);

        return $this;
    }

    /**
     * @param string|array|null $glue
     * @return string
     */
    public function prepareGlue($glue)
    {
        if (is_object($glue)) {
            return null;
        }

        if (is_array($glue)) {
            foreach ($glue as &$gluePart) {
                $gluePart = $this->prepareGlue($gluePart);
            }
            $glue = implode('', $glue);
        }

        if ($glue === '\r' || $glue === 'return') {
            $glue = $this->getAllowReturnChar() ? "\r" : null;
        }

        if ($glue === '\n' || $glue === 'newline') {
            $glue = $this->getAllowNewlineChar() ? PHP_EOL : null;
        }
        
        if ($glue === '\t' || $glue === 'tab') {
            $glue = $this->getAllowTabChar() ? "\t" : null;
        }

        return $glue;
    }

    /**
     * Get prepend value
     *
     * @return null|string
     */
    public function getPrepend()
    {
        return $this->prepend;
    }

    /**
     * Set prepend value
     *
     * @param null|string|array $value
     * @return $this
     */
    public function setPrepend($value)
    {
        $this->prepend = $this->prepareGlue($value);

        return $this;
    }

    /**
     * @param string|null $value
     * @param string|null $prepend
     * @return string
     */
    public function prepend($value, $prepend = null)
    {
        if ($prepend === null) {
            $prepend = $this->getPrepend();
        }

        return $prepend . $value;
    }

    /**
     * Get append value
     *
     * @return null|string
     */
    public function getAppend()
    {
        return $this->append;
    }

    /**
     * Set append value
     *
     * @param null|string|array $value
     * @return $this
     */
    public function setAppend($value)
    {
        $this->append = $this->prepareGlue($value);

        return $this;
    }

    /**
     * @param string|null $value
     * @param string|null $append
     * @return string
     */
    public function append($value, $append = null)
    {
        if ($append === null) {
            $append = $this->getAppend();
        }

        return $value . $append;
    }

    /**
     * @return string
     */
    public function getValueWrapPattern()
    {
        return $this->valueWrapPattern;
    }

    /**
     * @param string|null $pattern
     * @return $this
     */
    public function setValueWrapPattern($pattern)
    {
        $this->valueWrapPattern = $pattern;

        return $this;
    }

    /**
     * @param string|null $field
     * @param string|null $value
     * @param string|null $pattern
     * @return string
     */
    public function wrapValue($field, $value, $pattern = null)
    {
        if ($pattern === null) {
            $pattern = $this->getValueWrapPattern();
        }

        return $this->convertPlaceholderValues(
            $field,
            $value,
            $pattern
        );
    }
    
    /**
     * @param null|string $field
     * @param null|string $value
     * @param null|string $pattern
     * @return string
     */
    public function convertPlaceholderValues(
        $field = null,
        $value = null,
        $pattern = null
    ) {
        $pairs['{{field}}'] = strtolower($field);
        $pairs['{{FIELD}}'] = strtoupper($field);
        $pairs['{{value}}']  = $value;
        $pairs['{{newline}}'] = $this->getAllowNewlineChar() ? PHP_EOL : null;
        $pairs['{{return}}']  = $this->getAllowReturnChar() ? "\r" : null;
        $pairs['{{tab}}'] = $this->getAllowTabChar() ? "\t" : null;

        return str_replace(
            array_keys($pairs),
            $pairs,
            $pattern
        );
    }

    /**
     * Get included fields
     *
     * @return array
     */
    public function getIncludedFields()
    {
        return $this->includedFields;
    }

    /**
     * Set included fields
     *
     * @param array $fields
     * @return $this
     */
    public function setIncludedFields(array $fields)
    {
        $this->includedFields = $fields;

        return $this;
    }
    
    /**
     * @param array $array
     */
    public function filterArrayByIncludedFields(array &$array)
    {
        if ($this->getIncludedFields()) {
            $newArray = [];
            foreach ($this->getIncludedFields() as $field => $null) {
                $newArray[$field] = isset($array[$field])
                    ? $array[$field] : null;
            }

            $array = $newArray;
        }
    }

    /**
     * @param array|DataObject $item
     * @return array|DataObject
     */
    public function filterByIncludedFields(&$item)
    {
        if ($this->getIncludedFields()) {
            is_array($item) ?
                $this->filterArrayByIncludedFields($item) :
                $this->filterObjectByIncludedFields($item);
        }

        return $item;
    }

    /**
     * @param DataObject $object
     */
    public function filterObjectByIncludedFields(DataObject &$object)
    {
        $data = $object->getData();
        $this->filterArrayByIncludedFields($data);
        
        $object->setData($data);
    }

    /**
     * Set excluded fields
     *
     * @param array $fields
     * @return $this
     */
    public function setExcludedFields(array $fields)
    {
        $this->excludedFields = $fields;

        return $this;
    }

    /**
     * @param array $array
     */
    public function filterArrayByExcludedFields(array &$array)
    {
        $this->modelData->removeElements(
            $array,
            $this->getExcludedFields()
        );
    }

    /**
     * Get excluded fields
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return $this->excludedFields;
    }

    /**
     * @param DataObject $object
     */
    public function filterObjectByExcludedFields(DataObject &$object)
    {
        $data = $object->getData();
        $this->filterArrayByExcludedFields($data);
        
        $object->setData($data);
    }

    /**
     * Set default values
     *
     * @param array $values
     * @return $this
     */
    public function setDefaultValues(array $values)
    {
        $this->defaultValues = $values;

        return $this;
    }

    /**
     * @param array|DataObject $item
     * @return array|DataObject
     */
    public function filterByExcludedFields(&$item)
    {
        if ($this->getExcludedFields()) {
            is_array($item) ?
                $this->filterArrayByExcludedFields($item) :
                $this->filterObjectByExcludedFields($item);
        }

        return $item;
    }
    
    /**
     * @param $item
     */
    public function applyDefaultValues(&$item)
    {
        if (is_array($item)) {
            foreach ($this->getDefaultValues() as $field => $value) {
                if (array_key_exists($field, $item)) {
                    if ($item[$field] === null
                        || trim($item[$field]) === ''
                    ) {
                        $item[$field] = $value;
                    }
                }
            }
        } elseif ($item instanceof DataObject) {
            foreach ($this->getDefaultValues() as $field => $value) {
                if ($item->hasData($field)
                    && ($item->getData($field) === null || trim($item->getData($field)) === '')
                ) {
                    $item->setData($field, $value);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }
    
    /**
     * @param string $format
     * @return $this
     * @throws LocalizedException
     */
    public function setFormat($format)
    {
        $formats = [
            'string',
            'array',
            'object'
        ];
        if (!in_array($format, $formats)) {
            throw new LocalizedException(
                __(
                    '%1 is not a valid format. Acceptable values are %2.',
                    $format,
                    implode(', ', $formats)
                )
            );
        }
        $this->format = $format;

        return $this;
    }


    /**
     * @param array|DataObject $item
     * @return string|array|DataObject
     */
    public function format($item)
    {
        if ($item === null || $item === false) {
            return null;
        }

        $fieldMapper = $this->getSystemFieldMapper();
        if ($fieldMapper) {
            $item = $fieldMapper->map($item);
        }

        $this->filterByIncludedFields($item);
        $this->filterByExcludedFields($item);
        $this->applyDefaultValues($item);

        if (is_array($item)) {
            $item = $this->objectFactory->create(
                ['data' => $item]
            );
        }

        $this->executeIterators($item);

        $array = $item->getData();

        $this->modelData->removeObjects($array);

        foreach ($array as $field => &$value) {
            if (is_array($value)) {
                $this->modelData->removeObjects($value);
                $this->modelData->removeArrays($value);
                $value = is_array($value) ? implode(
                    $this->getGlue(),
                    $value
                ) : $value;
            }

            if ($this->getValueWrapPattern()) {
                $value = $this->wrapValue(
                    $field,
                    $value,
                    $this->getValueWrapPattern()
                );
            }
        }

        $fieldMapper = $this->getCustomFieldMapper();
        if ($fieldMapper) {
            $array = $this->getCustomFieldMapper()
                ->map($array);
        }

        switch ($this->getFormat()) {
            case 'array':
                $result = $array;
                break;
            case 'object':
                $result = $this->objectFactory
                    ->create(['data' => $array]);
                break;
            default:
                $result = $this->append(
                    $this->getAppend(),
                    $this->prepend(
                        implode($this->getGlue(), $array),
                        $this->getPrepend()
                    )
                );
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
}
