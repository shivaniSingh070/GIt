<?php

namespace Amasty\ImportCore\Api\Event;

use Amasty\ImportCore\Api\Config\ImportConfigInterface;
use Amasty\ImportCore\Api\ImportResultInterface;

interface ImportEventInterface
{
    public function execute(
        ImportConfigInterface $importConfig,
        ImportResultInterface $importResult
    );

    public function getMeta(): array;
}
