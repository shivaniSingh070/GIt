<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Entity;

interface FileUploaderConfigInterface
{
    public function getFileUploader();

    public function getFileUploaderClass();
    public function setFileUploaderClass(string $class): void;

    public function setStoragePath(string $storagePath): void;
    public function getStoragePath(): string;
}
