<?php

namespace Amasty\ImportCore\Api\Source;

interface SourceGeneratorInterface
{
    public function generate(array $data): string;

    public function getExtension(): string;
}
