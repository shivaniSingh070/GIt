<?php

namespace Amasty\ExportCore\Export\Filter\Type\Date;

interface ConfigInterface
{
    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string $value
     *
     * @return \Amasty\ExportCore\Export\Filter\Type\Date\ConfigInterface
     */
    public function setValue(?string $value): ConfigInterface;
}
