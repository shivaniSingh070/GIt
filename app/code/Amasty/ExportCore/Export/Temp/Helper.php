<?php

namespace Amasty\ExportCore\Export\Temp;

/**
 * temporary solution
 */
class Helper
{
    /**
     * @var array
     */
    private $headerStructure;

    public function setHeaderStructure(array $headerStructure): Helper
    {
        $this->headerStructure = $headerStructure;

        return $this;
    }

    public function getHeaderStructure(): ?array
    {
        return $this->headerStructure;
    }
}
