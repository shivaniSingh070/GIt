<?php

namespace Amasty\ImportCore\Api;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;

interface BehaviorInterface
{
    /**
     * @param array $data
     * @param string|null $customIdentifier
     * @return BehaviorResultInterface list of processed ids
     */
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface;
}
