<?php

namespace Amasty\ExportCore\Api\Template;

interface TemplateConfigInterface
{
    public function get(string $type): array;

    public function all(): array;
}
