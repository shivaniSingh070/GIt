<?php

namespace Amasty\ImportCore\Import\Config\Profile;

use Amasty\ImportCore\Api\Config\Profile\ModifierInterface;
use Magento\Framework\DataObject;

class Modifier extends DataObject implements ModifierInterface
{
    const MODIFIER_CLASS = 'modifier_class';
    const MODIFIER_GROUP = 'modifier_group';
    const ARGUMENTS = 'arguments';

    public function getModifierClass(): string
    {
        return $this->getData(self::MODIFIER_CLASS);
    }

    public function setModifierClass(string $modifierClass)
    {
        $this->setData(self::MODIFIER_CLASS, $modifierClass);
    }

    public function getGroup(): string
    {
        return $this->getData(self::MODIFIER_GROUP);
    }

    public function setGroup($group)
    {
        $this->setData(self::MODIFIER_GROUP, $group);
    }

    public function getArguments(): ?array
    {
        return $this->getData(self::ARGUMENTS);
    }

    public function setArguments(?array $arguments)
    {
        $this->setData(self::ARGUMENTS, $arguments);
    }
}
