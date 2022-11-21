<?php

namespace Amasty\ExportCore\Export\Config\EntitySource;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;

interface EntitySourceInterface
{
    /**
     * @return EntityConfigInterface[]
     */
    public function get();
}
