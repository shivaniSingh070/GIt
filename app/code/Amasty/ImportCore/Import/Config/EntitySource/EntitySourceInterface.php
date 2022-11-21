<?php

namespace Amasty\ImportCore\Import\Config\EntitySource;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;

interface EntitySourceInterface
{
    /**
     * @return EntityConfigInterface[]
     */
    public function get();
}
