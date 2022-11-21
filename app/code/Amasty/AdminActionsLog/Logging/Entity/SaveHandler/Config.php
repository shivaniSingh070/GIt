<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler;

use Amasty\AdminActionsLog\Api\Logging\EntitySaveHandlerInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config implements EntitySaveHandlerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Framework\App\Config\Value $object */
        $object = $metadata->getObject();

        return [
            LogEntry::TYPE => LogEntryTypes::TYPE_EDIT,
            LogEntry::CATEGORY_NAME => __('System Config'),
            LogEntry::ELEMENT_ID => (int)$object->getId(),
            LogEntry::STORE_ID => (int)$object->getScopeId()
        ];
    }

    /**
     * @param \Magento\Framework\App\Config\Value $object
     * @return array
     */
    public function processBeforeSave($object): array
    {
        return [
            $object->getPath() => $this->scopeConfig->getValue($object->getPath())
        ];
    }

    /**
     * @param \Magento\Framework\App\Config\Value $object
     * @return array
     */
    public function processAfterSave($object): array
    {
        return [
            $object->getPath() => $object->getValue()
        ];
    }
}
