<?php

namespace Amasty\ExportCore\Api\VirtualField;

interface GeneratorInterface
{
    /**
     * @param array $currentRecord
     * @return mixed
     */
    public function generateValue(array $currentRecord);
}
