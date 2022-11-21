<?php

namespace Amasty\ImportCore\Api\FileResolver;

interface FileResolverConfigInterface
{
    public function get(string $type): array;

    public function all(): array;
}
