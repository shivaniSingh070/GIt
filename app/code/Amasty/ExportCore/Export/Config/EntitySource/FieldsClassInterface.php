<?php

namespace Amasty\ExportCore\Export\Config\EntitySource;

interface FieldsClassInterface
{
    public function execute(
        \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface $existingConfig,
        \Amasty\ExportCore\Export\Config\EntityConfig $entityConfig
    ): \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
}
