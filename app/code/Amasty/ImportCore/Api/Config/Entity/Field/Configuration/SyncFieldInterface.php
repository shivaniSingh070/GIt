<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Entity\Field\Configuration;

interface SyncFieldInterface
{
    public function setEntityName(string $entityName): void;
    public function getEntityName(): string;

    public function setFieldName(string $fieldName): void;
    public function getFieldName(): string;

    public function setSynchronizationFieldName(string $fieldName): void;
    public function getSynchronizationFieldName(): string;
}
