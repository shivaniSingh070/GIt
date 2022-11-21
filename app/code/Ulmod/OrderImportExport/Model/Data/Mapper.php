<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data;

use Ulmod\OrderImportExport\Model\Data as ModelData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

class Mapper
{
    /**
     * @var ModelData
     */
    private $modelData;

    /**
     * @var array
     */
    private $mapping = [];

    /**
     * @param ModelData $modelData
     * @param array $extendMappings
     * @param array $mapping
     */
    public function __construct(
        ModelData $modelData,
        array $extendMappings = [],
        array $mapping = []
    ) {
        $this->modelData = $modelData;
        $class        = self::class;
        foreach ($extendMappings as $mapper) {
            if ($mapper instanceof $class) {
                $fielMap = $mapper->getMapping();
                foreach ($fielMap as $oldField => $newField) {
                    $this->addMapping(
                        $oldField,
                        $newField
                    );
                }
            }
        }

        foreach ($mapping as $oldField => $newField) {
            $this->addMapping(
                $oldField,
                $newField
            );
        }
    }

    /**
     * Set mapping
     *
     * @param array $mapping
     * @return $this
     * @throws LocalizedException
     */
    public function setMapping(array $mapping)
    {
        $this->validateMapping($mapping);
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @param array $mapping
     * @return bool
     * @throws LocalizedException
     */
    public function validateMapping(array $mapping)
    {
        foreach ($mapping as $field) {
            if (!is_string($field)) {
            }
        }

        return true;
    }

    /**
     * Get mapping
     *
     * @return array
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param string $oldField
     * @param string $newField
     * @return $this
     */
    public function addMapping($oldField, $newField)
    {
        $this->mapping[$oldField] = $newField;
        $this->validateMapping($this->mapping);

        return $this;
    }

    /**
     * @param string $oldField
     * @return string|null
     */
    public function getMappedField($oldField)
    {
        if ($this->isMapped($oldField)) {
            return $this->mapping[$oldField];
        }

        return null;
    }

    /**
     * @param string $oldField
     * @return $this
     */
    public function removeMapping($oldField)
    {
        if ($this->isMapped($oldField)) {
            unset($this->mapping[$oldField]);
        }

        return $this;
    }

    /**
     * @param DataObject $object
     * @param bool $keepOrigFields
     * @return DataObject
     */
    public function mapObject(
        DataObject &$object,
        $keepOrigFields = false
    ) {
        $data = $object->getData();
        $validKeys = array_merge(
            array_keys($data),
            array_values($this->getMapping())
        );

        $allData = [];
        
        $paths = $this->modelData->stringifyPaths($data);
        foreach ($paths as $key) {
            $allData[$key] = $object->getData($key);
        }

        $dataMapped = $this->mapArray(
            $allData,
            $keepOrigFields
        );
        
        foreach ($dataMapped as $key => &$value) {
            if (!in_array($key, $validKeys)) {
                unset($dataMapped[$key]);
            }
        }

        return $object->setData($dataMapped);
    }

    /**
     * @param string $oldField
     * @return bool
     */
    public function isMapped($oldField)
    {
        return array_key_exists(
            $oldField,
            $this->mapping
        );
    }

    /**
     * @param array|DataObject $data
     * @param bool  $keepOrigFields
     * @return array|DataObject
     */
    public function map(&$data, $keepOrigFields = false)
    {
        if (is_array($data)) {
            $data = $this->mapArray(
                $data,
                $keepOrigFields
            );
        } elseif ($data instanceof DataObject) {
            $data = $this->mapObject(
                $data,
                $keepOrigFields
            );
        }

        return $data;
    }
    
    /**
     * @param array $array
     * @param bool  $keepOrigFields
     * @return array
     */
    public function mapArray(array &$array, $keepOrigFields = false)
    {
        $dataMapped = [];
        foreach ($array as $oldField => $value) {
            $newField = $this->getMappedField($oldField);
            $mapField = $newField === null ? $oldField : $newField;
            if ($keepOrigFields) {
                $dataMapped[$oldField] = $value;
            }

            if (is_array($mapField)) {
                foreach ($mapField as $subfield) {
                    $dataMapped[$subfield] = $value;
                }
            } else {
                $dataMapped[$mapField] = $value;
            }
        }

        return $array = $dataMapped;
    }
}
