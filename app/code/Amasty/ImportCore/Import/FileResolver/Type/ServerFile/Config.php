<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\ServerFile;

class Config implements ConfigInterface
{
    private $filename;

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }
}
