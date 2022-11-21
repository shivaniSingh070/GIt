<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\UploadFile;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @param string $hash
     *
     * @return void
     */
    public function setHash(string $hash): void;
}
