<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class EavAttribute extends Common
{
    protected $dataKeysIgnoreList = [
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $metadata->getObject();

        return [
            LogEntry::ITEM => $attribute->getName(),
            LogEntry::CATEGORY => 'catalog/product_attribute/edit',
            LogEntry::CATEGORY_NAME => __('Product Attribute'),
            LogEntry::ELEMENT_ID => (int)$attribute->getId(),
            LogEntry::STORE_ID => (int)$attribute->getStoreId(),
            LogEntry::PARAMETER_NAME => 'attribute_id'
        ];
    }
}
