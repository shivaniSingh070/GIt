<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Customer;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Attribute\Collection;

class Customer extends Common
{
    const CATEGORY = 'customer/index/edit';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected $dataKeysIgnoreList = [
        'assistance_allowed',
        'entity_id',
        'rp_token',
        'rp_token_created_at',
        'ignore_validation_flag',
        'password_hash',
        'failures_num',
        'dob_is_formated',
        'updated_at'
    ];

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);
        $this->customerRepository = $customerRepository;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Customer\Model\Backend\Customer $customer */
        $customer = $metadata->getObject();
        $type = $customer->hasData(Collection::EAV_CODE_PASSWORD_HASH)
            ? LogEntryTypes::TYPE_EDIT
            : LogEntryTypes::TYPE_NEW;

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => $customer->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Customer'),
            LogEntry::ELEMENT_ID => (int)$customer->getId(),
            LogEntry::STORE_ID => (int)$customer->getStoreId()
        ];
    }

    public function processBeforeSave($object): array
    {
        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $this->customerRepository->getById($object->getId());

        return $this->filterObjectData($customer->__toArray());
    }
}
