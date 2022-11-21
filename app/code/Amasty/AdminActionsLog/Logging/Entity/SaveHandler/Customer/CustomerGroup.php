<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Customer;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class CustomerGroup extends Common
{
    const CATEGORY = 'customer/group/edit';

    protected $dataKeysIgnoreList = [
        'tax_class_name'
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Customer\Model\Group $group */
        $group = $metadata->getObject();

        return [
            LogEntry::ITEM => $group->getCustomerGroupCode(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Customer Group'),
            LogEntry::ELEMENT_ID => (int)$group->getId(),
        ];
    }
}
