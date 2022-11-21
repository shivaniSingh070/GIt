<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Entity;

interface IndexerConfigInterface
{
    public function setIndexerClass(string $class): void;
    public function getIndexer();

    public function setApplyType(string $type): void;
    public function getApplyType(): string;

    public function setIndexerMethods(array $methods): void;
    public function getIndexerMethodByBehavior(string $behaviorCode): ?string;
}
