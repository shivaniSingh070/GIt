<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Model\Batch\ResourceModel;

use Amasty\ImportCore\Model\Batch\Batch;
use Amasty\ImportCore\Model\Batch\ResourceModel\Batch as BatchResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Batch::class, BatchResource::class);
    }
}
