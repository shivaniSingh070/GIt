<?php

namespace Amasty\ImportCore\Import\Config\RelationSource;

use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;

interface RelationSourceInterface
{
    /**
     * @return RelationConfigInterface[]
     */
    public function get();
}
