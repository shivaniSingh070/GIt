<?php

namespace Amasty\ImportCore\Api\Modifier;

interface RelationModifierInterface
{
    /**
     * @param array &$entityRow
     * @param array &$subEntityRows
     * @return array
     */
    public function transform(array &$entityRow, array &$subEntityRows): array;
}
