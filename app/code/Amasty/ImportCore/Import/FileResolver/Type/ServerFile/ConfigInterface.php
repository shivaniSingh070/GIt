<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\ServerFile;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getFilename(): string;

    /**
     * @param $filename
     *
     * @return void
     */
    public function setFilename(string $filename): void;
}
