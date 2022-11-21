<?php

namespace Amasty\Feed\Model\Field\DataProvider;

use Amasty\Feed\Model\Field\ResourceModel\CollectionFactory;

/**
 * Class Listing
 *
 * @package Amasty\Feed
 */
class Listing extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
