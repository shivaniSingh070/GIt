<?php

namespace Amasty\ImportCore\Api\Modifier;

use Amasty\ImportCore\Api\Config\Profile\FieldInterface;
use Amasty\ImportCore\Api\Config\Profile\ModifierInterface;

interface FieldModifierInterface
{
    public function transform($value);

    public function prepareArguments(FieldInterface $field, $requestData): array;

    public function getJsConfig(): array;

    public function getValue(ModifierInterface $modifier): array;

    public function getGroup(): string;
}
