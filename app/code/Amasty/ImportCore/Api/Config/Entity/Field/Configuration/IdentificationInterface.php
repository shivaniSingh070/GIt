<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Entity\Field\Configuration;

interface IdentificationInterface
{
    public function setIsIdentifier(bool $isIdentifier): void;
    public function isIdentifier(): bool;

    public function setLabel(string $label): void;
    public function getLabel(): string;
}
