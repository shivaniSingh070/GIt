<?php

namespace Amasty\ExportCore\Export\Filter\Type\Text;

interface ConfigInterface
{
    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string|null $value
     *
     * @return \Amasty\ExportCore\Export\Filter\Type\Text\ConfigInterface
     */
    public function setValue(?string $value): ConfigInterface;
}
