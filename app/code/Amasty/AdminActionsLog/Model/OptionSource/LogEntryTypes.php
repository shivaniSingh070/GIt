<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\OptionSource;

class LogEntryTypes implements \Magento\Framework\Data\OptionSourceInterface
{
    const TYPE_NEW = 'new';
    const TYPE_EDIT = 'edit';
    const TYPE_DELETE = 'delete';
    const TYPE_CACHE = 'cache';
    const TYPE_EXPORT = 'export';
    const TYPE_RESTORE = 'restore';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $optionArray = [];

        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::TYPE_NEW => __('New'),
            self::TYPE_EDIT => __('Edit'),
            self::TYPE_DELETE => __('Delete'),
            self::TYPE_CACHE => __('Cache'),
            self::TYPE_EXPORT => __('Export'),
            self::TYPE_RESTORE => __('Restore'),
        ];
    }
}
