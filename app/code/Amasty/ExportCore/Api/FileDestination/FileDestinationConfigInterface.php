<?php

namespace Amasty\ExportCore\Api\FileDestination;

interface FileDestinationConfigInterface
{
    public function get(string $type): array;

    public function all(): array;
}
