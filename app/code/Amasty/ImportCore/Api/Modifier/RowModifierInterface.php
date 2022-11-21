<?php

namespace Amasty\ImportCore\Api\Modifier;

interface RowModifierInterface
{
    /**
     * @param array &$row
     * @return mixed
     */
    public function transform(array &$row): array;
}
