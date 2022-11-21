<?php

namespace Amasty\ExportCore\Api\PostProcessing;

use Amasty\ExportCore\Api\ExportProcessInterface;

interface ProcessorInterface
{
    public function process(ExportProcessInterface $exportProcess): ProcessorInterface;
}
