<?php

namespace Amasty\ImportCore\Api\Action;

use Amasty\ImportCore\Api\ImportProcessInterface;

interface FileUploaderInterface
{
    public function initialize(ImportProcessInterface $importProcess): void;
    public function execute(ImportProcessInterface $importProcess): void;
}
