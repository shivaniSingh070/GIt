<?php

namespace Amasty\ImportCore\Api\FileResolver;

use Amasty\ImportCore\Api\ImportProcessInterface;

interface FileResolverInterface
{
    public function execute(ImportProcessInterface $importProcess): string;
}
