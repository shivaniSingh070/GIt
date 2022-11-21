<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Sales;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Rma extends Common
{
    const CATEGORY = 'admin/rma/edit';

    /**
     * @param MetadataInterface $metadata
     * @return array
     */
    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Rma\Model\Rma $rma */
        $rma = $metadata->getObject();

        return [
            LogEntry::ITEM => __('Return Request #%1', $rma->getId()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('RMA'),
            LogEntry::ELEMENT_ID => (int)$rma->getId()
        ];
    }
}
