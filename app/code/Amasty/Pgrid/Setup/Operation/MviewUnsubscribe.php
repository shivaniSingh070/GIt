<?php

declare(strict_types=1);

namespace Amasty\Pgrid\Setup\Operation;

use Amasty\Pgrid\Model\Indexer;

class MviewUnsubscribe
{
    /**
     * @var Indexer\QtySoldProcessor
     */
    private $qtySoldProcessor;

    public function __construct(Indexer\QtySoldProcessor $qtySoldProcessor)
    {
        $this->qtySoldProcessor = $qtySoldProcessor;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $this->qtySoldProcessor->getIndexer()->getView()->unsubscribe();
    }
}
