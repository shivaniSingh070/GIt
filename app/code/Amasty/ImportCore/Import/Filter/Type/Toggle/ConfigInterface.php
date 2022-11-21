<?php

namespace Amasty\ImportCore\Import\Filter\Type\Toggle;

interface ConfigInterface
{
    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string|null $value
     *
     * @return \Amasty\ImportCore\Import\Filter\Type\Toggle\ConfigInterface
     */
    public function setValue(?string $value): ConfigInterface;
}
