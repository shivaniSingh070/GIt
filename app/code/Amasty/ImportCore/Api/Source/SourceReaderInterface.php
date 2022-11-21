<?php

namespace Amasty\ImportCore\Api\Source;

use Amasty\ImportCore\Api\ImportProcessInterface;

interface SourceReaderInterface
{
    public function initialize(ImportProcessInterface $importProcess);

    /**
     * Returns array with row data or false if end of file reached
     * @return array|bool
     */
    public function readRow();

    /**
     * @return int
     */
    public function estimateRecordsCount(): int;
}
