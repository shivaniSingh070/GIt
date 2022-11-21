<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Model\Data\Formatter;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\DataObject;
use Ulmod\OrderImportExport\Model\Data as ModelData;
use Ulmod\OrderImportExport\Model\Data\FormatterInterface;
use Ulmod\OrderImportExport\Model\Data\Mapper as SystemFieldMapper;
use Ulmod\OrderImportExport\Model\Data\Mapper as CustomFieldMapper;
        
class Iterator extends \Ulmod\OrderImportExport\Model\Data\Formatter
{
    /**
     * @var FormatterInterface
     */
    private $formatter;

    public function __construct(
        DataObjectFactory $objectFactory,
        ModelData $modelData,
        FormatterInterface $formatter,
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
        parent::__construct(
            $objectFactory,
            $modelData,
            $systemFieldMapper,
            $customFieldMapper,
            $iterators,
            $format,
            $prepend,
            $glue,
            $append,
            $valueWrapPattern,
            $excludedFields,
            $includedFields,
            $defaultValues,
            $allowNewlineChar,
            $allowTabChar,
            $allowReturnChar
        );
        $this->formatter = $formatter;
    }

    /**
     * @param array|DataObject $items
     * @return array|DataObject
     */
    public function iterate($items)
    {
        if (is_array($items)) {
            foreach ($items as &$itemData) {
                $itemData = $this->formatter->format($itemData);
                $wrapPattern = $this->getValueWrapPattern();
                if ($wrapPattern) {
                    $itemData = $this->wrapValue(
                        null,
                        $itemData,
                        $wrapPattern
                    );
                }
            }
            $items = $this->append(
                $this->prepend(
                    implode($this->getGlue(), $items),
                    $this->getPrepend()
                ),
                $this->getAppend()
            );
        }

        if ($items instanceof DataObject) {
            $data = $items->getData();
            foreach ($data as &$itemData) {
                $itemData = $this->formatter->format($itemData);
                $wrapPattern = $this->getValueWrapPattern();
                if ($wrapPattern) {
                    $itemData = $this->wrapValue(
                        null,
                        $itemData,
                        $wrapPattern
                    );
                }
            }
            $items = $this->append(
                $this->prepend(
                    implode($this->getGlue(), $data),
                    $this->getPrepend()
                ),
                $this->getAppend()
            );
        }

        return $items;
    }
}
