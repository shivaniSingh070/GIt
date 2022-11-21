<?php

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Integration\TestModule\Model\ResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\ExportCore\Test\Integration\TestModule\Model\TestEntity1::class,
            TestEntity1::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
