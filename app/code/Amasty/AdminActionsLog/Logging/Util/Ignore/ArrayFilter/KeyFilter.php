<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;

class KeyFilter
{
    public function filter(array $data, array $keysToIgnore): array
    {
        if (empty($keysToIgnore)) {
            return $data;
        }

        return array_filter($data, function ($key) use ($keysToIgnore) {
            return !in_array($key, $keysToIgnore);
        }, ARRAY_FILTER_USE_KEY);
    }
}
