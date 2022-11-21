<?php

namespace Amasty\ExportCore\Api\Template;

use Amasty\ExportCore\Api\ChunkStorageInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;

interface RendererInterface
{
    public function render(
        ExportProcessInterface $exportProcess,
        ChunkStorageInterface $chunkStorage
    ): RendererInterface;

    public function getFileExtension(ExportProcessInterface $exportProcess): ?string;
}
