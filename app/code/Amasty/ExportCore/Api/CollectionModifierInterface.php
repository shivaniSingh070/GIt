<?php

namespace Amasty\ExportCore\Api;

use Amasty\ExportCore\Api\ExportProcessInterface;

interface CollectionModifierInterface
{
    public function apply(\Magento\Framework\Data\Collection $collection)
        : \Amasty\ExportCore\Api\CollectionModifierInterface;
}
