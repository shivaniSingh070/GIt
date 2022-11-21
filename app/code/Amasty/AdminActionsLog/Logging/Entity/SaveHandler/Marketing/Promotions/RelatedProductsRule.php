<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Marketing\Promotions;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class RelatedProductsRule extends Common
{
    const CATEGORY = 'admin/targetrule/edit';

    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\TargetRule\Model\Rule $relatedProductsRule */
        $relatedProductsRule = $metadata->getObject();

        return [
            LogEntry::ITEM => $relatedProductsRule->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Related Products Rule'),
            LogEntry::ELEMENT_ID => (int)$relatedProductsRule->getId(),
            LogEntry::STORE_ID => (int)$relatedProductsRule->getStoreId()
        ];
    }
}
