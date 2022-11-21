<?php

namespace Amasty\ImportCore\Api\Filter;

interface FilterConfigInterface
{
    public function get(string $type): array;
    public function all(): array;
}
