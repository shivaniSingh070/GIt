<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Marketing\Promotions;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class CatalogPriceRule extends Common
{
    const CATEGORY = 'catalog_rule/promo_catalog/edit';

    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\CatalogRule\Model\Rule $catalogPriceRule */
        $catalogPriceRule = $metadata->getObject();

        return [
            LogEntry::ITEM => $catalogPriceRule->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Catalog Price Rule'),
            LogEntry::ELEMENT_ID => (int)$catalogPriceRule->getId()
        ];
    }
}
