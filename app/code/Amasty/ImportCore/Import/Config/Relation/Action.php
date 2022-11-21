<?php

namespace Amasty\ImportCore\Import\Config\Relation;

use Amasty\ImportCore\Api\Config\Relation\RelationActionInterface;
use Magento\Framework\DataObject;

class Action extends DataObject implements RelationActionInterface
{
    const ACTION_CLASS = 'action_class';

    public function getConfigClass()
    {
        return $this->getData(self::ACTION_CLASS);
    }

    public function setConfigClass($configClass)
    {
        $this->setData(self::ACTION_CLASS, $configClass);
    }
}
