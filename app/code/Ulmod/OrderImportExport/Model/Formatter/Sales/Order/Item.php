<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Formatter\Sales\Order;

use Ulmod\OrderImportExport\Model\Data\Formatter;
use Ulmod\OrderImportExport\Model\Data\FormatterInterface;
use Magento\Framework\DataObjectFactory;
use Ulmod\OrderImportExport\Model\Data as ModelData;
use Ulmod\OrderImportExport\Model\Data\Mapper as SystemFieldMapper;
use Ulmod\OrderImportExport\Model\Data\Mapper as CustomFieldMapper;
use Magento\Framework\DataObject;
        
class Item extends Formatter
{
    /**
     * @var FormatterInterface[]
     */
    private $formatters;

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
        $allowReturnChar = true,
        array $formatters = []
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

        $this->setFormatters($formatters);
    }

    /**
     * @param array $formatters
     * @return $this
     */
    public function setFormatters(array $formatters)
    {
        $this->formatters = $formatters;

        return $this;
    }

    /**
     * @param string $type
     * @param FormatterInterface $formatter
     * @return $this
     */
    public function addFormatter(
        $type,
        FormatterInterface $formatter
    ) {
        $this->formatters[$type] = $formatter;

        return $this;
    }

    /**
     * @return array|FormatterInterface[]
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * @param string $type
     *
     * @return bool|FormatterInterface|mixed
     */
    public function getFormatter($type)
    {
        if (isset($this->formatters[$type])) {
            return $this->formatters[$type];
        }

        return false;
    }

    /**
     * @param array|DataObject $item
     * @return null|string
     */
    public function format($item)
    {
        $productType = $item->getProductType();
        $formatter = $this->getFormatter($productType);
        if ($formatter instanceof FormatterInterface) {
            return $formatter->format($item);
        }

        return null;
    }
}
