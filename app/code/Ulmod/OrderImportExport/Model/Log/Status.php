<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Log;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const STATUS_SUCCESS = 0;
    const STATUS_FAILED  = 1;
    const STATUS_WARNING  = 2;

    protected $options = null;
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null == $this->options) {
            $this->options = [
                ['value' => self::STATUS_SUCCESS, 'label' => __('SUCCESS')],
                ['value' => self::STATUS_FAILED, 'label' => __('FAILED')],
                ['value' => self::STATUS_WARNING, 'label' => __('WARNING')],
            ];
        }

        return $this->options;
    }
}
