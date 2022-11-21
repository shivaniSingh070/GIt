<?php

namespace Amasty\ImportCore\Import\Source\Type\Xml;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getItemPath();

    /**
     * @param string $itemPath
     *
     * @return void
     */
    public function setItemPath($itemPath);
}
