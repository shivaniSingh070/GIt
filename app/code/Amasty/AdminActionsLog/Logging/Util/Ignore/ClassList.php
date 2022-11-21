<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Util\Ignore;

use Amasty\AdminActionsLog\Logging\Util\ClassNameNormalizer;

class ClassList
{
    /**
     * @var ClassNameNormalizer
     */
    private $classNameNormalizer;

    /**
     * @var array
     */
    private $list;

    public function __construct(
        ClassNameNormalizer $classNameNormalizer,
        array $list = []
    ) {
        $this->classNameNormalizer = $classNameNormalizer;
        $this->list = array_map(function ($class) {
            return trim($class, '\\');
        }, $list);
    }

    public function isIgnored(string $class): bool
    {
        return in_array($this->classNameNormalizer->execute($class), $this->list);
    }
}
