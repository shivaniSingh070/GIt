<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Cms;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Block extends Common
{
    const CATEGORY = 'cms/block/edit';

    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Cms\Model\Block $block */
        $block = $metadata->getObject();

        return [
            LogEntry::ITEM => $block->getTitle(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('CMS Block'),
            LogEntry::ELEMENT_ID => (int)$block->getId(),
            LogEntry::STORE_ID => (int)$block->getStoreId(),
            LogEntry::PARAMETER_NAME => 'block_id'
        ];
    }
}
