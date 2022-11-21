<?php

namespace Amasty\ExportCore\Export\Filter\Type\Toggle;

interface ConfigInterface
{
    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string|null $value
     *
     * @return \Amasty\ExportCore\Export\Filter\Type\Toggle\ConfigInterface
     */
    public function setValue(?string $value): ConfigInterface;
}
