<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\DiffFinder;

interface DiffFinderAdapterInterface
{
    /**
     * Compare 2 strings and return result string with marked differences.
     *
     * @param string $fromText
     * @param string $toText
     * @return string
     */
    public function render(string $fromText, string $toText): string;
}
