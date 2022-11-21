<?php

namespace Amasty\ImportCore\Api\Source;

interface SourceConfigInterface
{
    public function get(string $type): array;

    public function all(): array;
}
