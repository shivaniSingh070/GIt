<?php

namespace Amasty\ImportCore\Api\Event;

use Amasty\ImportCore\Api\Config\ImportConfigInterface;
use Amasty\ImportCore\Api\ImportResultInterface;

interface BeforeBatchImportInterface
{
    public function execute(
        ImportConfigInterface $importConfig,
        ImportResultInterface $importResult,
        array &$data
    );

    public function getMeta(): array;
}
