<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling;

use Amasty\ImportCore\Api\Config\Entity\Field\ActionInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\ActionInterfaceFactory;
use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\FieldModifier\EavOptionLabel2OptionValue;
use Amasty\ImportCore\Import\DataHandling\FieldModifier\EmptyToNull;
use Amasty\ImportCore\Import\DataHandling\FieldModifier\Str2Float;
use Amasty\ImportCore\Import\Utils\Config\ArgumentConverter;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Ui\Component\Form\Element\MultiSelect;

class FieldModifierResolver
{
    /**
     * @var array
     */
    private $isNotNeedCreateEavModifier = ['store_id'];

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var ActionInterfaceFactory
     */
    private $actionFactory;

    /**
     * @var ArgumentConverter
     */
    private $argumentsConverter;

    /**
     * @var array
     */
    private $systemArguments;

    public function __construct(
        ConfigClassInterfaceFactory $configClassFactory,
        ActionInterfaceFactory $actionFactory,
        ArgumentConverter $argumentsConverter
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->actionFactory = $actionFactory;
        $this->argumentsConverter = $argumentsConverter;
        $this->systemArguments = $this->argumentsConverter->toArguments(['system' => true]);
    }

    /**
     * Resolve fields actions using DB table column description (retrieved by DESCRIBE mysql command)
     *
     * @param array $fieldDetails
     * @param array $existingActions
     * @return ActionInterface[]
     */
    public function resolveByDbColumnInfo(array $fieldDetails, array $existingActions): array
    {
        $modifierConfigs = $this->getModifierConfigsByDbColInfo($fieldDetails);
        $modifierActions = [];

        foreach ($modifierConfigs as $modifierConfig) {
            $action = $this->actionFactory->create();
            $action->setConfigClass($modifierConfig)
                ->setGroup('beforeValidate');

            $modifierActions[] = $action;
        }

        return $this->mergeActions($modifierActions, $existingActions);
    }

    private function getModifierConfigsByDbColInfo(array $fieldDetails): array
    {
        $modifiers = [];
        switch ($fieldDetails['DATA_TYPE']) {
            case 'int':
                $modifiers[] = $this->createModifierConfig(EmptyToNull::class, $this->systemArguments);
                break;
            case 'decimal':
                $modifiers[] = $this->createModifierConfig(EmptyToNull::class, $this->systemArguments);
                $modifiers[] = $this->createModifierConfig(Str2Float::class, $this->systemArguments);
                break;
            case 'timestamp':
                if ($fieldDetails['NULLABLE'] || !empty($fieldDetails['DEFAULT'])) {
                    $modifiers[] = $this->createModifierConfig(EmptyToNull::class, $this->systemArguments);
                }
                break;
        }

        return $modifiers;
    }

    /**
     * Resolve fields actions for eav attribute
     *
     * @param AttributeInterface $attribute
     * @param array $existingActions
     * @return ActionInterface[]
     */
    public function resolveByEavAttribute(AttributeInterface $attribute, array $existingActions): array
    {
        $modifierActions = [];
        $modifierConfigs = $this->getModifierConfigsByEavAttr($attribute);
        foreach ($modifierConfigs as $modifierConfig) {
            $action = $this->actionFactory->create();
            $action->setConfigClass($modifierConfig)
                ->setGroup('beforeValidate');

            $modifierActions[] = $action;
        }

        return $this->mergeActions($modifierActions, $existingActions);
    }

    private function getModifierConfigsByEavAttr(AttributeInterface $attribute): array
    {
        $modifiers = [];
        /** @var AbstractAttribute $attribute */
        if ($attribute->isAllowedEmptyTextValue(AbstractAttribute::EMPTY_STRING)) {
            $modifiers[] = $this->createModifierConfig(EmptyToNull::class, $this->systemArguments);
        }
        if ($attribute->getBackendType() == 'decimal') {
            $modifiers[] = $this->createModifierConfig(Str2Float::class, $this->systemArguments);
        }

        if ($attribute->usesSource() && !in_array($attribute->getAttributeCode(), $this->isNotNeedCreateEavModifier)) {
            $modifiers[] = $this->createModifierConfig(
                EavOptionLabel2OptionValue::class,
                $this->argumentsConverter->toArguments([
                    'isMultiselect' => $attribute->getFrontendInput() === MultiSelect::NAME,
                    'preselected' => true,
                    'eavEntityType' => $attribute->getEntityType()->getEntityTypeCode(),
                    'field' => $attribute->getAttributeCode()
                ])
            );
        }

        return $modifiers;
    }

    private function createModifierConfig($className, array $arguments = [])
    {
        return $this->configClassFactory->create([
            'baseType' => FieldModifierInterface::class,
            'name' => $className,
            'arguments' => $arguments
        ]);
    }

    private function mergeActions(array $modifierActions, array $existingActions): array
    {
        $existingActionNames = $this->getActionNames($existingActions);

        /** @var \Amasty\ImportCore\Api\Config\Entity\Field\ActionInterface $newAction */
        foreach ($modifierActions as $newAction) {
            if (in_array($newAction->getConfigClass()->getName(), $existingActionNames)) {
                continue;
            }
            $existingActions[] = $newAction;
        }

        return $existingActions;
    }

    private function getActionNames(array $actions): array
    {
        $names = [];
        foreach ($actions as $action) {
            $names[] = $action->getConfigClass()->getName();
        }

        return $names;
    }
}
