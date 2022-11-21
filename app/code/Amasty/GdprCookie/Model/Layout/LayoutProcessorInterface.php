<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Model\Layout;

interface LayoutProcessorInterface
{
    public function process(array $jsLayout): array;
}
