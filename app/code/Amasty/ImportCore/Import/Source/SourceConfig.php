<?php

namespace Amasty\ImportCore\Import\Source;

use Amasty\ImportCore\Api\Source\SourceConfigInterface;

class SourceConfig implements SourceConfigInterface
{
    /**
     * @var array
     */
    private $sourceConfig = [];

    public function __construct(array $sourceConfig)
    {
        foreach ($sourceConfig as $config) {
            if (!isset($config['code'], $config['readerClass'])) {
                throw new \LogicException('Import source "' . $config['code'] . ' is not configured properly');
            }
            $this->sourceConfig[$config['code']] = $config;
        }
    }

    public function get(string $type): array
    {
        if (!isset($this->sourceConfig[$type])) {
            throw new \RuntimeException('Source "' . $type . '" is not defined');
        }

        return $this->sourceConfig[$type];
    }

    public function all(): array
    {
        return $this->sourceConfig;
    }
}
