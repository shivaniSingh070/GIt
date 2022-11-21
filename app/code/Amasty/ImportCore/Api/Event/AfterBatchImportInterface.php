<?php

namespace Amasty\ImportCore\Api\Event;

use Amasty\ImportCore\Api\Config\ImportConfigInterface;
use Amasty\ImportCore\Api\ImportResultInterface;

interface AfterBatchImportInterface
{
    public function execute(
        ImportConfigInterface $importConfig,
        ImportResultInterface $importResult,
        array &$data,
        array $result
    );

    public function getMeta(): array;
}
