<?php

namespace Amasty\ImportCore\Api\Config\Entity\Field;

interface ActionInterface
{
    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface
     */
    public function getConfigClass();

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface $configClass
     * @return $this
     */
    public function setConfigClass($configClass);

    /**
     * @return string
     */
    public function getGroup();

    /**
     * @param string $group
     * @return $this
     */
    public function setGroup($group);
}
