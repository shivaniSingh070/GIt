<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\DataHandling;

use Amasty\ImportCore\Api\Config\Entity\Field\ActionInterfaceFactory;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Api\Modifier\RelationModifierInterface;
use Amasty\ImportCore\Api\Modifier\RowModifierInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Config\RelationConfigProvider;
use Amasty\ImportCore\Import\DataHandling\ActionConfigBuilder;
use Amasty\ImportCore\Import\DataHandling\FieldModifier\DefaultValue;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterfaceFactory;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;

class DataHandlerProvider
{
    /**
     * @var ConfigClassFactory
     */
    private $configClassFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configFactory;

    /**
     * @var ArgumentInterfaceFactory
     */
    private $argumentFactory;

    public function __construct(
        ConfigClassFactory $configClassFactory,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider,
        ConfigClassInterfaceFactory $configFactory,
        ArgumentInterfaceFactory $argumentFactory
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
        $this->configFactory = $configFactory;
        $this->argumentFactory = $argumentFactory;
    }

    /**
     * Get field modifiers registry.
     * The result includes modifiers for profile entities and sub entities
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param string $modifierGroup
     * @return FieldModifierInterface[][]
     */
    public function getFieldModifiersRegistry(
        EntitiesConfigInterface $profileEntitiesConfig,
        $modifierGroup
    ): array {
        $result = [];
        $this->collectEntityFieldModifiers(
            $profileEntitiesConfig,
            $modifierGroup,
            $result
        );

        $this->collectProfileFieldModifiers(
            $profileEntitiesConfig,
            $modifierGroup,
            $result
        );

        return $result;
    }

    /**
     * Collect entity field modifiers
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param string $modifierGroup
     * @param array $modifiers
     * @return void
     */
    private function collectEntityFieldModifiers(
        EntitiesConfigInterface $profileEntitiesConfig,
        $modifierGroup,
        array &$modifiers
    ) {
        $entityCode = $profileEntitiesConfig->getEntityCode();
        if (!isset($modifiers[$entityCode])) {
            $entityConfig = $this->entityConfigProvider->get($entityCode);

            $modifiers[$entityCode] = [];
            $fieldConfig = $entityConfig->getFieldsConfig()->getFields();

            if ($fieldConfig) {
                foreach ($fieldConfig as $field) {
                    foreach ((array)$field->getActions() as $action) {
                        if (!$action->getConfigClass() || $action->getGroup() != $modifierGroup
                            || $this->isPreselected(($action->getConfigClass()->getArguments()))
                        ) {
                            continue;
                        }
                        $modifiers[$entityCode][$field->getName()][] = $this->configClassFactory->createObject(
                            $action->getConfigClass()
                        );
                    }
                }
            }
        }

        foreach ($profileEntitiesConfig->getSubEntitiesConfig() as $subEntitiesConfig) {
            $this->collectEntityFieldModifiers(
                $subEntitiesConfig,
                $modifierGroup,
                $modifiers
            );
        }
    }

    /**
     * Collect profile field modifiers
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param string $modifierGroup
     * @param array $modifiers
     * @return void
     */
    private function collectProfileFieldModifiers(
        EntitiesConfigInterface $profileEntitiesConfig,
        $modifierGroup,
        array &$modifiers
    ) {
        $entityCode = $profileEntitiesConfig->getEntityCode();
        $profileFields = $profileEntitiesConfig->getFields() ?: [];

        $defaultValueFields = $this->getFieldsWithDefaultValue($profileEntitiesConfig);

        foreach ($profileFields as $field) {
            if (isset($defaultValueFields[$field->getName()])) {
                $modifiers[$entityCode][$field->getName()][] =
                    $this->createDefaultValueModifier($defaultValueFields[$field->getName()]);
                continue;
            }
            foreach ($field->getModifiers() as $fieldModifier) {
                if ($fieldModifier->getGroup() != $modifierGroup) {
                    continue;
                }
                $configClass = $this->configFactory->create(
                    [
                        'name' => $fieldModifier->getModifierClass(),
                        'baseType' => FieldModifierInterface::class,
                        'arguments' => $fieldModifier->getArguments() ?? []
                    ]
                );
                $modifiers[$entityCode][$field->getName()][] = $this->configClassFactory->createObject($configClass);
            }
        }

        foreach ($profileEntitiesConfig->getSubEntitiesConfig() as $subEntitiesConfig) {
            $this->collectProfileFieldModifiers(
                $subEntitiesConfig,
                $modifierGroup,
                $modifiers
            );
        }
    }

    private function getFieldsWithDefaultValue(EntitiesConfigInterface $entitiesConfig)
    {
        $fields = [];

        foreach ($entitiesConfig->getFields() as $field) {
            if (!$field->getValue()) {
                continue;
            }
            $fields[$field->getName()] = $field->getValue();
        }

        return $fields;
    }

    private function createDefaultValueModifier(string $value): FieldModifierInterface
    {
        $valueArgument = $this->argumentFactory->create();
        $valueArgument->setType('string');
        $valueArgument->setName('value');
        $valueArgument->setValue($value);

        $configClass = $this->configFactory->create(
            [
                'name' => DefaultValue::class,
                'baseType' => FieldModifierInterface::class,
                'arguments' => [$valueArgument]
            ]
        );

        return $this->configClassFactory->createObject($configClass);
    }

    /**
     * Get relation modifiers registry
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param string $modifierGroup
     * @return RelationModifierInterface[][]
     */
    public function getRelationModifiersRegistry(
        EntitiesConfigInterface $profileEntitiesConfig,
        $modifierGroup
    ): array {
        $result = [];
        if ($modifierGroup == DataHandlingAction::GROUP_BEFORE_VALIDATE) {
            $this->collectRelationModifiers($profileEntitiesConfig, $result);
        }

        return $result;
    }

    /**
     * Collect relation modifiers
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param array $modifiers
     * @return void
     */
    private function collectRelationModifiers(
        EntitiesConfigInterface $profileEntitiesConfig,
        array &$modifiers
    ) {
        $entityCode = $profileEntitiesConfig->getEntityCode();

        $subEntityConfigs = $profileEntitiesConfig->getSubEntitiesConfig();
        foreach ($subEntityConfigs as $subEntityConfig) {
            $relationConfig = $this->relationConfigProvider->getExact(
                $entityCode,
                $subEntityConfig->getEntityCode()
            );

            if ($relationConfig) {
                $action = $relationConfig->getAction();
                if ($action) {
                    $subEntityCode = $relationConfig->getChildEntityCode();
                    $modifiers[$entityCode][$subEntityCode] = $this->configClassFactory->createObject(
                        $action->getConfigClass()
                    );
                }
            }
        }

        foreach ($subEntityConfigs as $subEntitiesConfig) {
            $this->collectRelationModifiers(
                $subEntitiesConfig,
                $modifiers
            );
        }
    }

    /**
     * Get row modifiers registry
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param string $modifierGroup
     * @return RowModifierInterface[][]
     */
    public function getRowModifiersRegistry(
        EntitiesConfigInterface $profileEntitiesConfig,
        $modifierGroup
    ): array {
        $result = [];
        if ($modifierGroup == DataHandlingAction::GROUP_BEFORE_VALIDATE) {
            $this->collectRowModifiers($profileEntitiesConfig, $result);
        }

        return $result;
    }

    /**
     * Collect row modifiers
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param array $modifiers
     * @return void
     */
    private function collectRowModifiers(
        EntitiesConfigInterface $profileEntitiesConfig,
        array &$modifiers
    ) {
        $entityCode = $profileEntitiesConfig->getEntityCode();
        if (!isset($modifiers[$entityCode])) {
            $entityConfig = $this->entityConfigProvider->get($entityCode);

            $modifiers[$entityCode] = null;
            $actionClass = $entityConfig->getFieldsConfig()->getRowActionClass();

            if ($actionClass) {
                $modifierConfig = $this->configFactory->create([
                    'baseType' => RowModifierInterface::class,
                    'name' => $actionClass,
                    'arguments' => []
                ]);
                $modifiers[$entityCode] = $this->configClassFactory->createObject(
                    $modifierConfig
                );
            }
        }

        foreach ($profileEntitiesConfig->getSubEntitiesConfig() as $subEntitiesConfig) {
            $this->collectRowModifiers(
                $subEntitiesConfig,
                $modifiers
            );
        }
    }

    private function isPreselected(array $arguments): bool
    {
        return (bool)$this->findArgumentByName($arguments, ActionConfigBuilder::IS_PRESELECTED);
    }

    private function findArgumentByName(array $arguments, string $name)
    {
        foreach ($arguments as $argument) {
            if ($argument->getName() == $name) {
                return $argument->getValue();
            }
        }

        return '';
    }
}
