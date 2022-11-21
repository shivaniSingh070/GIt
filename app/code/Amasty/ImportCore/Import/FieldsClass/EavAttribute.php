<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\FieldsClass;

use Amasty\ImportCore\Import\Config\Eav\Attribute\OptionsConverter;
use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ImportCore\Import\Config\EntitySource\Xml\FieldsClassInterface;
use Amasty\ImportCore\Import\DataHandling\FieldModifierResolver;
use Amasty\ImportCore\Import\Filter\FilterConfigBuilder;
use Amasty\ImportCore\Import\Filter\FilterTypeResolver;
use Amasty\ImportCore\Import\Filter\Type\Select\Filter;
use Amasty\ImportCore\Import\Utils\MetadataSearcher;
use Amasty\ImportCore\Import\Validation\FieldValidationResolver;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\EntityMetadataInterface;

class EavAttribute implements FieldsClassInterface
{
    const MEDIA_FRONTEND_INPUT = ['media_image'];

    /**
     * @var array
     */
    private $mediaAttributeCodes = [];

    /**
     * @var FieldInterfaceFactory
     */
    private $fieldFactory;

    /**
     * @var FieldModifierResolver
     */
    private $fieldModifierResolver;

    /**
     * @var FieldValidationResolver
     */
    private $fieldValidationResolver;

    /**
     * @var FilterConfigBuilder
     */
    protected $filterConfigBuilder;

    /**
     * @var FilterTypeResolver
     */
    private $filterTypeResolver;

    /**
     * @var OptionsConverter
     */
    private $attributeOptionsConverter;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var EntityMetadataInterface|null
     */
    private $entityMetadata;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $attributeOptionsArgs = [];

    public function __construct(
        FieldInterfaceFactory $fieldFactory,
        FieldModifierResolver $fieldModifierResolver,
        FieldValidationResolver $fieldValidationResolver,
        FilterConfigBuilder $filterConfigBuilder,
        FilterTypeResolver $filterTypeResolver,
        OptionsConverter $attributeOptionsConverter,
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MetadataSearcher $metadataSearcher,
        $config = []
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->fieldModifierResolver = $fieldModifierResolver;
        $this->fieldValidationResolver = $fieldValidationResolver;
        $this->filterConfigBuilder = $filterConfigBuilder;
        $this->filterTypeResolver = $filterTypeResolver;
        $this->attributeOptionsConverter = $attributeOptionsConverter;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->entityMetadata = $metadataSearcher->searchMetadata(
            $config['entityType'] ?? '',
            MetadataSearcher::EAV_ENTITY_TYPE
        );
        $this->config = $config;
    }

    public function execute(FieldsConfigInterface $existingConfig): FieldsConfigInterface
    {
        $fields = [];

        $existingFields = $this->keyByFieldName($existingConfig->getFields());
        if (isset($existingFields['store_id'])) {
            $fields['store_id'] = $existingFields['store_id'];
        }
        $excludedFields = $this->getFieldNamesToRemove($existingFields);
        $this->setMediaAttributeCodes();
        $attributesByCode = $this->keyByAttributeCode(
            $this->getEavAttributes($excludedFields)
        );

        foreach ($existingFields as $field) {
            $fieldName = $field->getName();
            if ($field->getRemove() || isset($fields[$fieldName])) {
                continue;
            }

            if (isset($attributesByCode[$fieldName])) {
                $fields[] = $this->processAttribute($attributesByCode[$fieldName], $field);
                unset($attributesByCode[$fieldName]);
            } else {
                $fields[] = $field;
            }
        }
        foreach ($attributesByCode as $attributeCode => $attribute) {
            if (isset($fields[$attributeCode])) {
                continue;
            }
            $field = $this->fieldFactory->create();
            $fields[] = $this->processAttribute($attribute, $field);
        }

        if ($this->entityMetadata !== null && !$this->isIdentityFieldExists($fields)) {
            array_unshift($fields, $this->createIdentityField());
        }

        $existingConfig->setFields($fields);

        return $existingConfig;
    }

    /**
     * Build field filter config
     *
     * @param Attribute $attribute
     *
     * @return \Amasty\ImportCore\Api\Config\Entity\Field\FilterInterface|null
     */
    private function buildFilterConfig($attribute)
    {
        $filterType = $this->filterTypeResolver->getEavAttributeFilterType($attribute);
        $this->filterConfigBuilder->setFilterType($filterType);
        if ($filterType == Filter::TYPE_ID && $attribute->usesSource()) {
            $this->filterConfigBuilder->setMetaArguments(
                $this->getAttributeOptionsArguments($attribute)
            );
        }

        return $this->filterConfigBuilder->build();
    }

    /**
     * Get attribute options config arguments
     *
     * @param Attribute $attribute
     * @return ArgumentInterface[]
     */
    private function getAttributeOptionsArguments($attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        if (!isset($this->attributeOptionsArgs[$attributeCode])) {
            $options = $attribute->getSource()
                ->getAllOptions();
            $this->attributeOptionsArgs[$attributeCode] = array_merge(
                $this->attributeOptionsConverter->toConfigArguments(
                    $options,
                    'options'
                ),
                $this->attributeOptionsConverter->getConfigArgumentDataType($attribute)
            );
        }

        return $this->attributeOptionsArgs[$attributeCode];
    }

    private function isIdentityFieldExists(array $fields): bool
    {
        $identityFieldName = $this->entityMetadata->getLinkField();

        foreach ($fields as $field) {
            if ($field->getName() == $identityFieldName) {
                return true;
            }
        }

        return false;
    }

    private function createIdentityField(): FieldInterface
    {
        $columnInfo = ['DATA_TYPE' => 'int'];
        $identityField = $this->fieldFactory->create();
        $identityField->setName($this->entityMetadata->getLinkField());
        $identityField->setActions(
            $this->fieldModifierResolver->resolveByDbColumnInfo(
                $columnInfo,
                (array)$identityField->getActions()
            )
        );
        $identityField->setValidations(
            $this->fieldValidationResolver->resolveByDbColumnInfo(
                $columnInfo,
                (array)$identityField->getValidations()
            )
        );
        $identityField->setIsIdentity(true);

        return $identityField;
    }

    private function processAttribute(AttributeInterface $attribute, FieldInterface $field): FieldInterface
    {
        $field->setName($attribute->getAttributeCode());
        $field->setActions(
            $this->fieldModifierResolver->resolveByEavAttribute($attribute, (array)$field->getActions())
        );
        $field->setValidations(
            $this->fieldValidationResolver->resolveByEavAttribute($attribute, (array)$field->getValidations())
        );

        $field->setFilter($this->buildFilterConfig($attribute));
        $field->setIsFile(array_key_exists($attribute->getAttributeCode(), $this->mediaAttributeCodes));

        return $field;
    }

    /**
     * @param array $excludedAttrCodes
     * @return AttributeInterface[]
     */
    private function getEavAttributes(array $excludedAttrCodes)
    {
        if (!isset($this->config['entityType'])) {
            throw new \RuntimeException('entityType isn\'t specified.');
        }

        if (!empty($excludedAttrCodes)) {
            $criteria = $this->searchCriteriaBuilder
                ->addFilter(AttributeInterface::ATTRIBUTE_CODE, $excludedAttrCodes, 'nin')
                ->create();
        } else {
            $criteria = $this->searchCriteriaBuilder->create(); //to avoid `NOT IN (NULL)` in query
        }

        return $this->attributeRepository->getList($this->config['entityType'], $criteria)
            ->getItems();
    }

    /**
     * @return AttributeInterface[]
     */
    private function setMediaAttributeCodes()
    {
        if (!empty($this->mediaAttributeCodes)) {
            return $this->mediaAttributeCodes;
        }
        if (!isset($this->config['entityType'])) {
            throw new \RuntimeException('entityType isn\'t specified.');
        }
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeInterface::FRONTEND_INPUT, self::MEDIA_FRONTEND_INPUT, 'in')
            ->create();
        $this->mediaAttributeCodes = $this->keyByAttributeCode(
            $this->attributeRepository->getList($this->config['entityType'], $criteria)->getItems()
        );

        return $this->mediaAttributeCodes;
    }

    /**
     * Get fields names/attribute codes to remove
     *
     * @param FieldInterface[] $fields
     * @return array
     */
    private function getFieldNamesToRemove(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            if ($field->getRemove()) {
                $result[] = $field->getName();
            }
        }

        return $result;
    }

    /**
     * Key attributes by attribute code
     *
     * @param AttributeInterface[] $attributes
     * @return AttributeInterface[]
     */
    private function keyByAttributeCode(array $attributes): array
    {
        $result = [];
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = $attribute;
        }

        return $result;
    }

    /**
     * Key field configs by field name
     *
     * @param FieldInterface[] $fields
     * @return FieldInterface[]
     */
    protected function keyByFieldName(array $fields): array
    {
        $result = [];
        foreach ($fields as $fieldConfig) {
            $result[$fieldConfig->getName()] = $fieldConfig;
        }

        return $result;
    }
}
