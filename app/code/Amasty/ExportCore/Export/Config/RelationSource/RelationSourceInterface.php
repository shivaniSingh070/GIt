<?php

namespace Amasty\ExportCore\Export\Config\RelationSource;

use Amasty\ExportCore\Api\Config\Relation\RelationInterface;

interface RelationSourceInterface
{
    /**
     * @return RelationInterface[]
     */
    public function get();
}
