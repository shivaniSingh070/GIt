<?php

namespace Amasty\ExportCore\Export\Filter\Type\Store;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    private $value;

    public function getValue(): ?array
    {
        return $this->value;
    }

    public function setValue(?array $value): ConfigInterface
    {
        $this->value = $value;

        return $this;
    }
}
