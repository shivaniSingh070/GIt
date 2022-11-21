<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Util;

class ClassNameNormalizer
{
    public function execute(string $className): string
    {
        return str_replace(['\\Interceptor', '\\Proxy'], '', $className);
    }
}
