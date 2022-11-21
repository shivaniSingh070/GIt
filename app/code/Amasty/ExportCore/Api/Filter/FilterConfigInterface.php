<?php

namespace Amasty\ExportCore\Api\Filter;

interface FilterConfigInterface
{
    public function get(string $type): array;
    public function all(): array;
}
