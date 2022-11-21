<?php

namespace Amasty\ImportCore\Api\Action;

use Amasty\ImportCore\Api\ImportProcessInterface;

interface CleanerInterface
{
    /**
     * Performs data cleanup
     *
     * @param ImportProcessInterface $importProcess
     * @return void
     */
    public function clean(ImportProcessInterface $importProcess): void;
}
