<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation\OutOfRangeValidator;

use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;

class SmallInt implements FieldValidatorInterface
{
    const MAX_VALUE = 32767;
    const MIN_VALUE = -32768;

    public function validate(array $row, string $field): bool
    {
        if (isset($row[$field])) {
            return self::MIN_VALUE <= (int)$row[$field] && self::MAX_VALUE >= (int)$row[$field];
        }

        return true;
    }
}
