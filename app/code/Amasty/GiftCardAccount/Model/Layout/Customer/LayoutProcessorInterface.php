<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Layout\Customer;

interface LayoutProcessorInterface
{
    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process(array $jsLayout): array;
}
