<?php

namespace Amasty\ImportExportCore\Api\Config\ConfigClass;

interface ConfigClassInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface[]
     */
    public function getArguments(): ?array;
}
