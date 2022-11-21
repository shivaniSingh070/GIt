<?php

namespace Amasty\ImportCore\Import\Filter\Type\Store;

interface ConfigInterface
{
    /**
     * @return string[]|null
     */
    public function getValue(): ?array;

    /**
     * @param string[] $value
     *
     * @return \Amasty\ImportCore\Import\Filter\Type\Store\ConfigInterface
     */
    public function setValue(?array $value): ConfigInterface;
}
