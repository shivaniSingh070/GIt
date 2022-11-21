<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Customer;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class CustomerSegment extends Common
{
    const CATEGORY = 'customersegment/index/edit';

    protected $dataKeysIgnoreList = [
        'id',
        'segment_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\CustomerSegment\Model\Segment $segment */
        $segment = $metadata->getObject();

        return [
            LogEntry::ITEM => $segment->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Customer Segment'),
            LogEntry::ELEMENT_ID => (int)$segment->getId(),
        ];
    }
}
