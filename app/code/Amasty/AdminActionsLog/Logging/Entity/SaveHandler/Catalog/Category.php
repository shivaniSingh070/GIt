<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Category extends Common
{
    const CATEGORY = 'catalog/category/edit';

    protected $dataKeysIgnoreList = [
        'form_key',
        'entity_id'
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $metadata->getObject();

        if (!$category->getName()) {
            $category->load($category->getId()); // Force reload category in cases of mass delete, etc.
        }

        return [
            LogEntry::ITEM => $category->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Catalog Category'),
            LogEntry::ELEMENT_ID => (int)$category->getId(),
            LogEntry::STORE_ID => (int)$category->getStoreId()
        ];
    }
}
