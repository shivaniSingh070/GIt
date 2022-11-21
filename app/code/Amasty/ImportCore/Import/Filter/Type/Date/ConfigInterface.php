<?php

namespace Amasty\ImportCore\Import\Filter\Type\Date;

interface ConfigInterface
{
    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string $value
     *
     * @return \Amasty\ImportCore\Import\Filter\Type\Date\ConfigInterface
     */
    public function setValue(?string $value): ConfigInterface;
}
