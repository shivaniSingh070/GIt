<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Api\Data\ImportConfigInterface;

abstract class AbstractProcessor
{
    /**
     * @var ImportConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $excludedFields;

    /**
     * Order constructor.
     *
     * @param array $excludedFields
     */
    public function __construct($excludedFields = [])
    {
        $this->excludedFields = $excludedFields;
        if ($excludedFields && current($excludedFields) === null) {
            $this->excludedFields = array_keys($excludedFields);
        }
    }

    /**
     * @return ImportConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ImportConfigInterface $config
     * @return $this
     */
    public function setConfig(ImportConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }
    
    /**
     * @param object|array $item
     * @return $this
     */
    protected function removeExcludedFields(&$item)
    {
        if (is_object($item)
            && method_exists($item, 'unsetData')
        ) {
            foreach ($this->excludedFields as $field) {
                $item->unsetData($field);
            }
        } elseif (is_array($item)) {
            foreach ($this->excludedFields as $field) {
                if (array_key_exists($field, $item)) {
                    unset($item[$field]);
                }
            }
        }

        return $this;
    }
}
