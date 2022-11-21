<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Cms;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Page extends Common
{
    const CATEGORY = 'admin/cms_page/edit/';

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $metadata->getObject();

        return [
            LogEntry::ITEM => $page->getTitle(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('CMS Page'),
            LogEntry::ELEMENT_ID => (int)$page->getId(),
            LogEntry::STORE_ID => (int)$page->getStoreId(),
            LogEntry::PARAMETER_NAME => 'page_id'
        ];
    }
}
