<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\TestModule\Model\ResourceModel;

use Amasty\ImportCore\Test\Integration\TestModule\Model\TestEntity1 as TestEntity1Model;

class TestEntity1Inserter extends \Amasty\ImportCore\Import\Behavior\InsertResourceModel
{
    const TABLE_NAME = 'amasty_import_test_entity1';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, TestEntity1Model::ID);
    }
}
