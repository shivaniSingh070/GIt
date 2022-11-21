<?php

namespace Amasty\ImportCore\Import\Config\EntitySource\Xml;

use Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface;

interface FieldsClassInterface
{
    public function execute(FieldsConfigInterface $existingConfig): FieldsConfigInterface;
}
