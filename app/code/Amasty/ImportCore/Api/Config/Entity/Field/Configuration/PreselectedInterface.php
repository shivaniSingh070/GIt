<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Entity\Field\Configuration;

interface PreselectedInterface
{
    public function setIsRequired(bool $isRequired): void;
    public function getIsRequired(): bool;

    public function setIncludeBehaviors(array $includeBehaviors): void;
    public function getIncludeBehaviors(): ?array;

    public function setExcludeBehaviors(array $excludeBehaviors): void;
    public function getExcludeBehaviors(): ?array;
}
