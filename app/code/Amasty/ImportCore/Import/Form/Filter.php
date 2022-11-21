<?php

namespace Amasty\ImportCore\Import\Form;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterfaceFactory;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Config\RelationConfigProvider;
use Amasty\ImportCore\Import\Utils\Hash;
use Amasty\ImportExportCore\Config\ConfigClass\Factory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;

class Filter implements \Amasty\ImportCore\Api\FormInterface
{
    /**
     * @var Factory
     */
    private $configClassFactory;

    /**
     * @var FieldFilterInterfaceFactory
     */
    private $filterFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var Hash
     */
    private $hash;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var string
     */
    private $excludeParentImage;

    public function __construct(
        FieldFilterInterfaceFactory $filterFactory,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider,
        Factory $configClassFactory,
        Repository $assetRepo,
        Hash $hash
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->filterFactory = $filterFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
        $this->assetRepo = $assetRepo;
        $this->hash = $hash;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $this->arguments = $arguments;
        $result = [
            'filter' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label']) ? __($arguments['label']) : __('Filter')),
                            'componentType' => 'fieldset',
                            'dataScope' => 'filter',
                            'additionalClasses' => 'amimportcore-filters-fieldset',
                            'visible' => true,
                        ]
                    ]
                ],
                'children' => [
                    'notice' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => null,
                                    'visible' => true,
                                    'componentType' => 'container',
                                    'formElement' => 'container',
                                    'additionalClasses' => 'amimportcore-filters-notice',
                                    'template' => 'ui/form/components/complex',
                                    'text' => __(
                                        'Only those fields that are included into mapping can be used for filtering.'
                                    )
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $result['filter']['children'] += $this->prepareEntitiesFilters(
            $entityConfig,
            $this->relationConfigProvider->get($entityConfig->getEntityCode()),
            0
        );

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        $result = [];
        if ($profileConfig->getEntitiesConfig()) {
            $result = $this->getFiltersData(
                $profileConfig->getEntityCode(),
                $profileConfig->getEntitiesConfig(),
                $this->relationConfigProvider->get($profileConfig->getEntityCode())
            );
        }
        if (!empty($result)) {
            return ['filter' => $result];
        }

        return [];
    }

    public function getFiltersData(
        string $entityCode,
        EntitiesConfigInterface $entitiesConfig,
        ?array $relationsConfig
    ) :array {
        $result = [$entityCode => []];
        if (!empty($entitiesConfig->getFilters())) {
            $id = 1;
            $entitiesFieldFilters = $this->getEntityFieldFilters($entityCode);
            foreach ($entitiesConfig->getFilters() as $filter) {
                if (!isset($result[$entityCode])) {
                    $result[$entityCode]['filters'] = [];
                }

                $value = $this->configClassFactory
                    ->createObject($entitiesFieldFilters[$filter->getField()]->getMetaClass())
                    ->getValue($filter);
                $result[$entityCode]['filters'][] = [
                    'id' => $id++,
                    'field' => $filter->getField(),
                    'condition' => $filter->getCondition(),
                    'value' => $value
                ];
            }
        }
        if (!empty($entitiesConfig->getSubEntitiesConfig()) && !empty($relationsConfig)) {
            foreach ($relationsConfig as $relation) {
                $currentSubEntitiesConfig = null;
                foreach ($entitiesConfig->getSubEntitiesConfig() as $subEntitiesConfig) {
                    if ($subEntitiesConfig->getEntityCode() == $relation->getChildEntityCode()) {
                        $currentSubEntitiesConfig = $subEntitiesConfig;
                        break;
                    }
                }
                if ($currentSubEntitiesConfig) {
                    $subEntityFilters = $this->getFiltersData(
                        $relation->getChildEntityCode(),
                        $currentSubEntitiesConfig,
                        $relation->getRelations()
                    );

                    if (!empty($subEntityFilters)) {
                        $result[$entityCode] += $subEntityFilters;
                    }
                }
            }
        }

        return array_filter($result);
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        if (!empty($requestFields = $request->getParam('filter'))) {
            $this->prepareProfileFilters(
                $profileConfig->getEntityCode(),
                $profileConfig->getEntitiesConfig(),
                $requestFields,
                $this->relationConfigProvider->get($profileConfig->getEntityCode())
            );
        }

        return $this;
    }

    public function prepareProfileFilters(
        $entityCode,
        EntitiesConfigInterface $entitiesConfig,
        array $data,
        ?array $relationsConfig
    ) {
        if (!empty($data[$entityCode]['filters'])) {
            $filters = [];
            $entityFieldFilters = $this->getEntityFieldFilters($entityCode);
            foreach ($data[$entityCode]['filters'] as $condition) {
                if (empty($condition['field'])) {
                    continue;
                }

                if (!empty($entityFieldFilters[$condition['field']])) {
                    $filter = $this->filterFactory->create();
                    $filter->setField($condition['field']);
                    $filter->setCondition($condition['condition'] ?? '');
                    $this->configClassFactory
                        ->createObject($entityFieldFilters[$condition['field']]->getMetaClass())
                        ->prepareConfig($filter, $condition['value'] ?? '');

                    $filters[] = $filter;
                }
            }
            $entitiesConfig->setFilters($filters);
        }

        if (!empty($relationsConfig)) {
            foreach ($entitiesConfig->getSubEntitiesConfig() as $subEntityConfig) {
                $currentRelation = false;
                foreach ($relationsConfig as $relation) {
                    if ($relation->getChildEntityCode() == $subEntityConfig->getEntityCode()) {
                        $currentRelation = $relation;
                        break;
                    }
                }
                if ($currentRelation) {
                    $this->prepareProfileFilters(
                        $currentRelation->getChildEntityCode(),
                        $subEntityConfig,
                        $data[$entityCode],
                        $currentRelation->getRelations()
                    );
                }
            }
        }
    }

    public function getEntityFieldFilters(string $entityCode): array
    {
        $entityFields = $this->entityConfigProvider->get($entityCode)
            ->getFieldsConfig()->getFields();
        $entityFieldFilters = [];
        foreach ($entityFields as $field) {
            $entityFieldFilters[$field->getName()] = $field->getFilter();
        }

        return $entityFieldFilters;
    }

    private function prepareEntitiesFilters(
        EntityConfigInterface $entityConfig,
        ?array $relationsConfig,
        int $level = 0,
        string $parentKey = ''
    ): array {
        $index = $this->hash->hash($parentKey . $entityConfig->getEntityCode());
        $result = [
            'filter_container.' . $index => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => $entityConfig->getName(),
                            'dataScope' => $entityConfig->getEntityCode(),
                            'componentType' => 'fieldset',
                            'visible' => true,
                            'collapsible' => true,
                            'opened' => false,
                            'template' => 'Amasty_ImportCore/form/fieldset'
                        ]
                    ]
                ],
                'children' => []
            ]
        ];

        $parent = &$result['filter_container.' . $index]['children'];

        $filterConfig = [];
        $fieldsOptions = [];

        $fields = $entityConfig->getFieldsConfig()->getFields();
        foreach ($fields as $field) {
            if ($field->getFilter()) {
                if ($field->getLabel()) {
                    $optionLabel = __($field->getLabel()) . '(' . $field->getMap() ?: $field->getName() . ')';
                } else {
                    $optionLabel = $field->getMap() ?: $field->getName();
                }
                $fieldsOptions[] = ['value' => $field->getName(), 'label' => $optionLabel];

                $metaClass = $this->configClassFactory->createObject($field->getFilter()->getMetaClass());
                $filterConfig[$field->getName()] = [
                    'config' => $metaClass->getJsConfig($field),
                    'conditions' => $metaClass->getConditions($field)
                ];
            }
        }

        $parent['addFilter'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'buttonClasses' => 'amimportcore-button  amimportcore-button-margin',
                        'component' => 'Amasty_ImportCore/js/form/components/button',
                        'title' => __('Add Filter'),
                        'componentType' => 'container',
                        'actions' => [
                            [
                                'targetName' => 'index = filter.' . $index . '.filters',
                                'actionName' => 'processingAddChild',
                                'params' => [false, false, false]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $parent['filter.' . $index . '.filters'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Amasty_ImportCore/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide amimportcore-dynamic-rows',
                        'componentType' => 'dynamicRows',
                        'recordTemplate' => 'record',
                        'addButton' => false,
                        'columnsHeader' => false,
                        'dataScope' => '',
                        'columnsHeaderAfterRender' => true,
                        'template' => 'ui/dynamic-rows/templates/default',
                        'filterConfig' => $filterConfig,
                        'renderDefaultRecord' => false,
                        'identificationProperty' => 'id',
                        'positionProvider' => 'position',
                        'dndConfig' => [
                            'enabled' => false
                        ]
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'isTemplate' => true,
                                'componentType' => 'container',
                                'dataScope' => 'filters',
                                'positionProvider' => 'position',
                                'is_collection' => true
                            ],
                            'template' => 'templates/container/default',
                        ],
                    ],
                    'children' => [
                        'field' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'select',
                                        'default' => true,
                                        'additionalClasses' => '-amwidth30 amimportcore-filter',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'dataScope' => 'field',
                                        'component' => 'Magento_Ui/js/form/element/ui-select',
                                        'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                                        'filterOptions' => true,
                                        'showCheckbox' => false,
                                        'multiple' => false,
                                        'disableLabel' => true,
                                        'label' => 'Field For Filtering',
                                        'sortOrder' => '10',
                                        'options' => $fieldsOptions
                                    ],
                                ],
                            ],
                        ],
                        'condition' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'component' => 'Amasty_ImportCore/js/condition-select',
                                        'elementTmpl' => 'Amasty_ImportCore/form/element/select',
                                        'formElement' => 'select',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'additionalClasses' => '-amwidth30 amimportcore-filter',
                                        'dataScope' => 'condition',
                                        'label' => 'Filter Condition',
                                        'sortOrder' => '20'
                                    ],
                                ],
                            ],
                        ],
                        'value' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'topParentContainer' => $index,
                                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                        'component' => 'Amasty_ImportCore/js/condition-value',
                                        'dataScope' => '',
                                        'componentType' => 'container',
                                        'additionalClasses' => '-amwidth30 amimportcore-filter',
                                        'label' => 'Value',
                                        'sortOrder' => '30',
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'component' => 'Magento_Ui/js/dynamic-rows/action-delete',
                                        'template' => 'ui/dynamic-rows/cells/action-delete',
                                        'additionalClasses' => 'amwidth40px data-grid-actions-cell amimportcore-remove',
                                        'componentType' => 'actionDelete',
                                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                        'dataType' => 'text',
                                        'label' => '',
                                        'sortOrder' => '80',
                                    ],
                                ],
                            ]
                        ],
                        'id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'input',
                                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                        'component' => 'Magento_Ui/js/form/element/text',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'dataScope' => 'id',
                                        'sortOrder' => '999',
                                        'visible' => false
                                    ],
                                ],
                            ],
                        ]
                    ],
                ],
            ]
        ];

        if ($relationsConfig) {
            foreach ($relationsConfig as $relation) {
                $parent += $this->prepareEntitiesFilters(
                    $this->entityConfigProvider->get($relation->getChildEntityCode()),
                    $relation->getRelations(),
                    $level + 1,
                    $index
                );
            }
        }

        return $result;
    }
}
