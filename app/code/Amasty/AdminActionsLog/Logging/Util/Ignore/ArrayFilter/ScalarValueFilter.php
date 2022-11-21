<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;

class ScalarValueFilter
{
    public function filter(array $data): array
    {
        return array_filter($data, function ($value) {
            return is_scalar($value) && !is_array($value);
        });
    }
}
