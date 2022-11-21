<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Api\Behavior;

interface BehaviorObserverInterface
{
    /**#@+
     * Behavior event types
     */
    const BEFORE_APPLY = 'beforeApply';
    const AFTER_APPLY = 'afterApply';
    /**#@-*/

    /**
     * Execute behavior observer logic
     *
     * @param array $data
     * @return void
     */
    public function execute(array &$data): void;
}
