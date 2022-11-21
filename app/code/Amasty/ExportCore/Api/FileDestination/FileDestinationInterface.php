<?php

namespace Amasty\ExportCore\Api\FileDestination;

use Amasty\ExportCore\Api\ExportProcessInterface;

interface FileDestinationInterface
{
    public function execute(ExportProcessInterface $exportProcess);
}
