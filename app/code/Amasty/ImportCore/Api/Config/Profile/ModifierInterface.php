<?php

namespace Amasty\ImportCore\Api\Config\Profile;

interface ModifierInterface
{
    /**
     * @return string
     */
    public function getModifierClass(): string;

    /**
     * @param string $modifierClass
     * @return void
     */
    public function setModifierClass(string $modifierClass);

    /**
     * @return string
     */
    public function getGroup(): string;

    /**
     * @param string $group
     * @return void
     */
    public function setGroup(string $group);

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface[]
     */
    public function getArguments(): ?array;

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface[]|null $arguments
     * @return void
     */
    public function setArguments(?array $arguments);
}
