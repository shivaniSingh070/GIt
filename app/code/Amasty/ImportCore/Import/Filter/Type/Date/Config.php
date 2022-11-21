<?php

namespace Amasty\ImportCore\Import\Filter\Type\Date;

class Config implements ConfigInterface
{
    /**
     * @var string|null
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
