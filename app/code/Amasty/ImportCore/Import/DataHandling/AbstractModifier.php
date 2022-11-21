<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling;

use Amasty\ImportCore\Api\Config\Profile\FieldInterface;
use Amasty\ImportCore\Api\Config\Profile\ModifierInterface;

abstract class AbstractModifier
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getValue(ModifierInterface $modifier): array
    {
        $modifierData = [];
        foreach ($modifier->getArguments() as $argument) {
            $modifierData[$argument->getName()] = $argument->getValue();
        }
        $modifierData['select_value'] = $modifier->getModifierClass();
        $modifierData['label'] = $this->getLabel();

        return $modifierData;
    }

    public function prepareArguments(FieldInterface $field, $requestData): array
    {
        return [];
    }

    public function getJsConfig(): array
    {
        return [
            'component' => 'Amasty_ImportCore/js/fields/modifier',
            'template' => 'Amasty_ImportCore/fields/modifier'
        ];
    }

    public function getGroup(): string
    {
        return ModifierProvider::TEXT_GROUP;
    }

    abstract public function getLabel(): string;
}
