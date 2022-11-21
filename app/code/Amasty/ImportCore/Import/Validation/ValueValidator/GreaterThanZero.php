<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation\ValueValidator;

use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;

class GreaterThanZero implements FieldValidatorInterface
{
    public function validate(array $row, string $field): bool
    {
        if (isset($row[$field]) && $row[$field] !== '') {
            return (float)$row[$field] > .0;
        }

        return true;
    }
}
