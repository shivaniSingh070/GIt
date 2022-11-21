<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Filtering;

use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Filter\FilterInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Filter\FilterConfig;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;
use Magento\Framework\ObjectManagerInterface;

class FilterProvider
{
    /**
     * @var FieldFilterFactory
     */
    private $fieldFilterFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var ConfigClassFactory
     */
    private $configClassFactory;

    /**
     * @var FilterConfig
     */
    private $filterConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        FieldFilterFactory $fieldFilterFactory,
        EntityConfigProvider $entityConfigProvider,
        ConfigClassFactory $configClassFactory,
        FilterConfig $filterConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->fieldFilterFactory = $fieldFilterFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->configClassFactory = $configClassFactory;
        $this->filterConfig = $filterConfig;
        $this->objectManager = $objectManager;
    }

    /**
     * Get field filters registry.
     * The result includes filters for profile entities and sub entities
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @return FilterInterface[][]
     */
    public function getFieldFilters(
        EntitiesConfigInterface $profileEntitiesConfig
    ): array {
        $result = [];
        $this->collectEntityFieldFilters(
            $profileEntitiesConfig,
            $result
        );

        return $result;
    }

    /**
     * Collect entity field filters
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param array $filters
     * @return void
     */
    private function collectEntityFieldFilters(
        EntitiesConfigInterface $profileEntitiesConfig,
        array &$filters
    ) {
        $entityCode = $profileEntitiesConfig->getEntityCode();
        if (!isset($filters[$entityCode])) {
            $filters[$entityCode] = [];
            $entityFields = $this->entityConfigProvider->get($entityCode)->getFieldsConfig()->getFields();
            $entityFilters = $profileEntitiesConfig->getFilters();

            if ($entityFilters) {
                $entityFieldsFilter = [];
                foreach ($entityFields as $field) {
                    $entityFieldsFilter[$field->getName()] = $field->getFilter();
                }

                foreach ($entityFilters as $entityFilter) {
                    $field = $entityFilter->getField();
                    $filter = null;
                    if (!empty($entityFilter->getType())) {
                        $filter = $this->getFilter($entityFilter->getType());
                    } elseif (!empty($entityFilter->getFilterClass())) {
                        $filter = $this->configClassFactory->createObject(
                            $entityFilter->getFilterClass()
                        );
                    } elseif (!empty($entityFieldsFilter[$field])) {
                        $filter = $this->configClassFactory->createObject(
                            $entityFieldsFilter[$field]->getFilterClass()
                        );
                    }

                    if (!empty($filter)) {
                        $filters[$entityCode][$field][] = $this->fieldFilterFactory->create(
                            [
                                'filter' => $filter,
                                'entityFilter' => $entityFilter
                            ]
                        );
                    }
                }
            }
        }

        foreach ($profileEntitiesConfig->getSubEntitiesConfig() as $subEntitiesConfig) {
            $this->collectEntityFieldFilters(
                $subEntitiesConfig,
                $filters
            );
        }
    }

    private function getFilter(string $type): FilterInterface
    {
        if (isset($this->filters[$type])) {
            return $this->filters[$type];
        }

        $filterClass = $this->filterConfig->get($type)['filterClass'];

        if (!is_subclass_of($filterClass, FilterInterface::class)) {
            throw new \RuntimeException('Wrong filter class: "' . $filterClass);
        }

        return $this->objectManager->create($filterClass);
    }
}
