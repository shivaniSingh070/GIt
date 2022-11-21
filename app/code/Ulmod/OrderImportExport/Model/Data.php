<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model;

use Magento\Framework\DataObject;

class Data
{
    /**
     * @param array $array
     */
    public function nullifyEmpty(array &$array)
    {
        $array = array_map(function ($value) {
            if (!is_array($value) && !is_object($value)
                    && $value === ''
                ) {
                $value = null;
            }

            return $value;
        }, $array);
    }

    /**
     * @param array $array
     */
    public function removeArrays(array &$array)
    {
        $array = array_filter(
            $array,
            function ($value) {
                return !is_object($value) && !is_array($value);
            }
        );
    }

    /**
     * @param array $array
     */
    public function removeObjects(array &$array)
    {
        $array = array_filter(
            $array,
            function ($value) {
                return !is_object($value);
            }
        );
    }

    /**
     * @param array $array
     * @param array $keys
     */
    public function removeElements(array &$array, array $keys)
    {
        foreach ($array as $key => &$value) {
            if (in_array($key, $keys)) {
                unset($array[$key]);
            }
        }
    }

    /**
     * Add header rows to array
     * Note that: $this->equalizeArrayKeys() should run first
     *
     * @param array $array
     */
    public function addHeadersRowToArray(array &$array)
    {
        reset($array);
        $row = current($array);
        if ($row) {
            $fields = array_keys($row);
            array_unshift($array, $fields);
        }
    }

    /**
     * Equalize array and
     * makes sure that all subarrays have the exact same keys
     *
     * @param array $array
     */
    public function equalizeArrayKeys(array &$array)
    {
        $fields = [];
        foreach ($array as &$subarray) {
            foreach ($subarray as $key => $value) {
                $fields[$key] = null;
            }
        }

        foreach ($array as &$subarray) {
            $newData = $fields;
            foreach ($fields as $field => $null) {
                if (isset($subarray[$field])) {
                    $newData[$field] = $subarray[$field];
                }
            }
            $subarray = $newData;
            $newData  = null;
        }
    }

    /**
     * Adds prefix to all array keys
     *
     * @param string $prefix
     * @param array|DataObject $item
     */
    public function addPrefix($prefix, &$item)
    {
        $isObject = $item instanceof DataObject;
        $array    = $isObject ? $item->getData() : $item;

        $newArray = [];
        foreach ($array as $key => &$value) {
            $newArray[$prefix . $key] = $value;
        }

        $array = $newArray;

        if ($isObject) {
            $item->setData($array);
        } else {
            $item = $array;
        }
    }

    /**
     * @param string $needle
     * @param string $haystack
     * @param bool   $caseSensitive
     * @return bool
     */
    public function startsWith(
        $needle,
        $haystack,
        $caseSensitive = true
    ) {
        $changer = $caseSensitive ? null : 'i';

        return (bool)preg_match('/^'. preg_quote($needle) .'/' . $changer, $haystack);
    }

    /**
     * @param array $array
     * @param array $path
     * @return array
     */
    public function stringifyPaths(array $array, array $path = [])
    {
        $result = [];
        foreach ($array as $key => $val) {
            $currentPath = array_merge($path, [$key]);
            if (is_array($val)) {
                $result[] = join('/', $currentPath);
                $result   = array_merge(
                    $result,
                    $this->stringifyPaths(
                        $val,
                        $currentPath
                    )
                );
            } else {
                $result[] = join('/', $currentPath);
            }
        }

        return $result;
    }

    /**
     * @param string $needle
     * @param string $haystack
     * @param bool   $caseSensitive
     * @return bool
     */
    public function endsWith(
        $needle,
        $haystack,
        $caseSensitive = true
    ) {
        $changer = $caseSensitive ? null : 'i';

        return (bool)preg_match('/'. preg_quote($needle) .'$/' . $changer, $haystack);
    }
}
