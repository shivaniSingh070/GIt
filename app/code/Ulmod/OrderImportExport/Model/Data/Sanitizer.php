<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data;

use Ulmod\OrderImportExport\Model\Data\SanitizerInterface;

class Sanitizer
{
    /**
     * @var array
     */
    private $fields;

    /**
     * @var SanitizerInterface[]
     */
    private $pool;

    /**
     * @param SanitizerInterface[] $pool
     * @param array $fields
     */
    public function __construct(
        $pool = [],
        $fields = []
    ) {
        $this->pool   = $pool;
        $this->fields = $fields;
    }

    /**
     * @param array $data
     * @return array
     */
    public function sanitize(array &$data)
    {
        foreach ($data as $key => &$value) {
            if (isset($this->fields[$key])) {
                $fieldKey = $this->fields[$key];
                if (isset($this->pool[$fieldKey])) {
                    $value = $this->pool[$fieldKey]->sanitize($value);
                }
            }
        }

        return $data;
    }
}
