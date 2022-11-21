<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Filter;

use Amasty\ImportCore\Api\Filter\FilterConfigInterface;

class FilterConfig implements FilterConfigInterface
{
    /**
     * @var array
     */
    private $filterConfig = [];

    public function __construct(array $filterConfig)
    {
        foreach ($filterConfig as $config) {
            if (!isset($config['code'], $config['filterClass'])) {
                throw new \LogicException('Filter "' . $config['code'] . ' is not configured properly');
            }
            $this->filterConfig[$config['code']] = $config;
        }
    }

    public function get(string $type): array
    {
        if (!isset($this->filterConfig[$type])) {
            throw new \RuntimeException('Filter "' . $type . '" is not defined');
        }

        return $this->filterConfig[$type];
    }

    public function all(): array
    {
        return $this->filterConfig;
    }
}
