<?php

declare(strict_types=1);

namespace Amasty\ExportCore\Export\Utils;

class CleanUpByProcessIdentity
{
    /**
     * @var TmpFileManagement
     */
    private $tmpFileManagement;

    public function __construct(
        TmpFileManagement $tmpFileManagement
    ) {
        $this->tmpFileManagement = $tmpFileManagement;
    }

    public function execute(string $processIdentity)
    {
        $this->tmpFileManagement->cleanFiles($processIdentity);
    }
}
