<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\AbstractModifier;
use Amasty\ImportCore\Import\DataHandling\ModifierProvider;

class Str2Float extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @param string $value
     * @return float|mixed
     */
    public function transform($value)
    {
        return is_numeric($value)
            ? floatval($value)
            : $value;
    }

    public function getGroup(): string
    {
        return ModifierProvider::TEXT_GROUP;
    }

    public function getLabel(): string
    {
        return __('Trim')->getText();
    }
}
