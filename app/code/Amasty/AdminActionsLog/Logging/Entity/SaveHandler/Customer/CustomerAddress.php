<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Customer;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;

class CustomerAddress extends Common
{
    const CATEGORY = 'customer/index/edit';

    protected $dataKeysIgnoreList = [
        'id',
        'customer_id',
        'store_id',
        'attribute_set_id'
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Customer\Model\Address $address */
        $address = $metadata->getObject();
        $type = $address->getOrigData() === null ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => $address->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Customer Address'),
            LogEntry::ELEMENT_ID => (int)$address->getCustomerId(),
        ];
    }
}
