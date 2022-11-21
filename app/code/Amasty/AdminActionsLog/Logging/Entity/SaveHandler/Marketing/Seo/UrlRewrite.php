<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Marketing\Seo;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class UrlRewrite extends Common
{
    const CATEGORY = 'admin/url_rewrite/edit';

    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite */
        $urlRewrite = $metadata->getObject();

        return [
            LogEntry::ITEM => $urlRewrite->getRequestPath(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('URL Rewrite'),
            LogEntry::ELEMENT_ID => (int)$urlRewrite->getId(),
            LogEntry::STORE_ID => (int)$urlRewrite->getStoreId()
        ];
    }
}
