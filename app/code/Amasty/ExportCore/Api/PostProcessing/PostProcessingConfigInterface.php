<?php

namespace Amasty\ExportCore\Api\PostProcessing;

interface PostProcessingConfigInterface
{
    public function get(string $type): array;
    public function all(): array;
}
