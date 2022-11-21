<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Sales;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;

class Shipment extends Common
{
    const CATEGORY = 'sales/order_shipment/view';

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $metadata->getObject();
        $type = $shipment->getOrigData() === null ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => __('Shipment for Order #%1', $shipment->getOrder()->getRealOrderId()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Shipment'),
            LogEntry::ELEMENT_ID => (int)$shipment->getId(),
            LogEntry::PARAMETER_NAME => 'shipment_id'
        ];
    }
}
