<?php

namespace Amasty\ExportCore\Export\Filter\Type\Store;

interface ConfigInterface
{
    /**
     * @return string[]|null
     */
    public function getValue(): ?array;

    /**
     * @param string[] $value
     *
     * @return \Amasty\ExportCore\Export\Filter\Type\Store\ConfigInterface
     */
    public function setValue(?array $value): ConfigInterface;
}
