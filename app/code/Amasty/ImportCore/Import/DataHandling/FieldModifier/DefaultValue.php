<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Config\Profile\FieldInterface;
use Amasty\ImportCore\Api\Config\Profile\ModifierInterface;
use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\AbstractModifier;
use Amasty\ImportCore\Import\DataHandling\ModifierProvider;
use Amasty\ImportCore\Import\Utils\Config\ArgumentConverter;

class DefaultValue extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var bool
     */
    private $force = false;

    private $value;

    /**
     * @var ArgumentConverter
     */
    private $argumentConverter;

    public function __construct($config, ArgumentConverter $argumentConverter)
    {
        parent::__construct($config);
        if (isset($config['force']) && $config['force']) {
            $this->force = $config['force'];
        }

        if (!isset($config['value'])) {
            throw new \LogicException('DefaultValue action value is not set');
        }

        $this->value = $config['value'];
        $this->argumentConverter = $argumentConverter;
    }

    public function transform($value)
    {
        if ($this->force) {
            return $this->value;
        }

        return ($value === null || $value === '') ? $this->value : $value;
    }

    public function getValue(ModifierInterface $modifier): array
    {
        $modifierData = [];
        foreach ($modifier->getArguments() as $argument) {
            $modifierData['value'][$argument->getName()] = $argument->getValue();
        }
        $modifierData['select_value'] = $modifier->getModifierClass();

        return $modifierData;
    }

    public function prepareArguments(FieldInterface $field, $requestData): array
    {
        return $this->argumentConverter->valueToArguments(
            !empty($requestData['value']['input_value']) ? (string)$requestData['value']['input_value'] : '',
            'input_value',
            'string'
        );
    }

    public function getGroup(): string
    {
        return ModifierProvider::TEXT_GROUP;
    }

    public function getLabel(): string
    {
        return __('Default Value')->getText();
    }

    public function getJsConfig(): array
    {
        return [
            'component' => 'Amasty_ImportCore/js/fields/modifier',
            'template' => 'Amasty_ImportCore/fields/modifier',
            'childTemplate' => 'Amasty_ImportCore/fields/1input-modifier',
            'childComponent' => 'Amasty_ImportCore/js/fields/modifier-field'
        ];
    }
}
