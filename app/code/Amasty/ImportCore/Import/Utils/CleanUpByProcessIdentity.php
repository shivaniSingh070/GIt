<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Utils;

use Amasty\ImportCore\Model\Batch\BatchRepository;

class CleanUpByProcessIdentity
{
    /**
     * @var TmpFileManagement
     */
    private $tmpFileManagement;

    /**
     * @var BatchRepository
     */
    private $batchRepository;

    public function __construct(
        BatchRepository $batchRepository,
        TmpFileManagement $tmpFileManagement
    ) {
        $this->tmpFileManagement = $tmpFileManagement;
        $this->batchRepository = $batchRepository;
    }

    public function execute(string $processIdentity): bool
    {
        $this->batchRepository->cleanup($processIdentity);
        $this->tmpFileManagement->cleanFiles($processIdentity);

        return true;
    }
}
