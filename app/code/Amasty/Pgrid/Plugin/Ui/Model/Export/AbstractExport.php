<?php

declare(strict_types=1);

namespace Amasty\Pgrid\Plugin\Ui\Model\Export;

use Magento\Catalog\Helper\Image;
use Magento\Framework\Api\Search;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Element;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export;

abstract class AbstractExport
{
    const DELIMITER = ', ';

    /**
     * @var string
     */
    protected $exportType = '';

    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * @var null
     */
    protected $sortedColumnMapping = null;

    /**
     * @var array
     */
    protected $fieldMappingOptions = [];

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Filesystem\File\WriteInterface
     */
    protected $directory;

    /**
     * @var Export\MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var ExcelFactory
     */
    protected $excelFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Export\SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var Element\UiComponent\Context
     */
    protected $context;

    /**
     * @var Search\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Search\SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var Image
     */
    protected $imageHelper;

    public function __construct(
        RequestInterface $request,
        Element\UiComponent\Context $context,
        Filesystem $filesystem,
        Filter $filter,
        Export\MetadataProvider $metadataProvider,
        ExcelFactory $excelFactory,
        Export\SearchResultIteratorFactory $iteratorFactory,
        Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterManager $filterManager,
        Random $random,
        Image $imageHelper,
        $pageSize = 200
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
        $this->pageSize = $pageSize;
        $this->request = $request;
        $this->context = $context;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterManager = $filterManager;
        $this->random = $random;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @return bool
     */
    public function checkNamespace(): bool
    {
        return $this->request->getParam('namespace') === 'product_listing';
    }

    /**
     * @param array $item
     * @return array
     */
    public function getRowXmlData($item): array
    {
        return $this->getRowData($item, $this->sortedColumnMapping);
    }

    /**
     * @param array $item
     * @param array $mapping
     * @return array
     */
    protected function getRowData(array $item, array &$mapping): array
    {
        $row = [];

        foreach ($mapping as $mappingField) {
            if (!isset($item[$mappingField])) {
                $row[] = '-';
                continue;
            }

            switch ($mappingField) {
                case 'thumbnail':
                    if (isset($item['thumbnail_src'])) {
                        $rowColumn = $item['thumbnail_src'];
                    } else {
                        /**
                         * Reinitialize $imageHelper on every datasource item
                         * @see \Magento\Catalog\Ui\Component\Listing\Columns\Thumbnail::prepareDataSource
                         */
                        $product = new \Magento\Framework\DataObject($item);
                        $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
                        $rowColumn = $imageHelper->getUrl();
                    }

                    break;
                case 'amasty_categories':
                    $rowColumn = $this->processCategoriesRowData($item[$mappingField]);
                    break;
                case 'quantity_per_source':
                case 'salable_quantity':
                    $rowColumn = $this->processStockRowData($item[$mappingField]);
                    break;
                default:
                    $rowColumn = $this->processDefaultRowData($item, $mappingField);
                    break;
            }

            $row[] = $this->filterManager->stripTags($rowColumn);
        }

        return $row;
    }

    private function processDefaultRowData(array $item, string $mappingField): string
    {
        if (isset($this->fieldMappingOptions[$mappingField])) {
            $options = $this->getItemOptionsAsArrayKeys($item[$mappingField]);
            $rowColumn = implode(
                static::DELIMITER,
                array_intersect_key($this->fieldMappingOptions[$mappingField], $options)
            );
        } else {
            $rowColumn = is_array($item[$mappingField])
                ? implode(static::DELIMITER, ConvertArray::toFlatArray($item[$mappingField]))
                : (string)$item[$mappingField];
        }

        return $rowColumn;
    }

    private function getItemOptionsAsArrayKeys($itemOptions)
    {
        if ($itemOptions && is_string($itemOptions)) {
            $options = array_filter(explode(',', $itemOptions));
            $options = array_map('trim', $options);
        } else {
            $options = ConvertArray::toFlatArray((array)$itemOptions);
        }

        return array_flip($options);
    }

    private function processCategoriesRowData(array $categories): string
    {
        $productCategories = array_map(function ($category) {
            return $category['path'] ?? $category['name'] ?? '';
        }, $categories);

        return implode(static::DELIMITER, $productCategories);
    }

    private function processStockRowData(array $stockData): string
    {
        $productStocks = array_map(function ($stock) {
            return sprintf('%s: %s', $stock['source_name'] ?? $stock['stock_name'], $stock['qty']);
        }, $stockData);

        return implode(static::DELIMITER, $productStocks);
    }

    /**
     * @param UiComponentInterface $component
     * @return array|null
     * @throws \Exception
     */
    protected function getSortedColumnFieldMapping(UiComponentInterface $component)
    {
        if ($this->sortedColumnMapping === null) {
            foreach ($this->getColumnsComponent($component)->getChildComponents() as $column) {
                if ($column->getData('config/label')
                    && $column->getData('name') !== 'actions'
                    && $column->getData('config/ampgrid/visible') === true
                ) {
                    $this->sortedColumnMapping[$column->getName()] = $column->getData('config/sortOrder') ?? 99999;
                }
            }

            asort($this->sortedColumnMapping);
            $this->sortedColumnMapping = array_keys($this->sortedColumnMapping);
        }

        return $this->sortedColumnMapping;
    }

    /**
     * Get header mapping array with aliases as values
     *
     * @param UiComponentInterface $component
     * @param array $sortedMapping
     * @return array
     */
    protected function getHeaders(UiComponentInterface $component, array $sortedMapping): array
    {
        $headerFieldMapping = array_combine(
            $this->metadataProvider->getFields($component),
            $this->metadataProvider->getHeaders($component)
        );

        return array_map(function ($field) use ($headerFieldMapping) {
            return $headerFieldMapping[$field];
        }, $sortedMapping);
    }

    /**
     * @param UiComponentInterface $component
     * @return UiComponentInterface|Columns
     * @throws LocalizedException
     */
    private function getColumnsComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof Columns) {
                return $childComponent;
            }
        }

        throw new LocalizedException(__('No columns found'));
    }

    protected function prepareFieldMappingOptions(UiComponentInterface $component, array $fieldMapping): void
    {
        $columnsComponent = $this->getColumnsComponent($component)->getChildComponents();

        foreach ($fieldMapping as $field) {
            if (isset($columnsComponent[$field])) {
                $options = $columnsComponent[$field]['config']['options'] ?? [];

                if ($options) {
                    $this->fieldMappingOptions[$field] = array_column($options, 'label', 'value');
                }
            }
        }
    }

    protected function getComponentProductItems(UiComponentInterface $component): array
    {
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $collection = $component->getContext()->getDataProvider()->getCollection();
        $collection->setPageSize(null);
        $dataProviderComponentConfig = $component->getContext()->getDataSourceData($component);
        $dataProviderName = $component->getContext()->getDataProvider()->getName();

        return $dataProviderComponentConfig[$dataProviderName]['config']['data']['items'] ?? [];
    }
}
