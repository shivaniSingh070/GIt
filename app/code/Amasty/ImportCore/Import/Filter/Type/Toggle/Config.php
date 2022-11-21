<?php

namespace Amasty\ImportCore\Import\Filter\Type\Toggle;

class Config implements ConfigInterface
{
    /**
     * @var string
     */
    private $value;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): ConfigInterface
    {
        $this->value = $value;

        return $this;
    }
}
