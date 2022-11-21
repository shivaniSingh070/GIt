<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Log;

use Magento\Framework\Data\OptionSourceInterface;

class TypeExport implements OptionSourceInterface
{
    const TYPE_EXPORT_ADMIN = 1;
    const TYPE_EXPORT_CLI = 2;    

    protected $options = null;
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null == $this->options) {
            $this->options = [
                ['value' => self::TYPE_EXPORT_ADMIN, 'label' => __('Admin GUI Export')],
                ['value' => self::TYPE_EXPORT_CLI, 'label' => __('CLI Export')],
            ];
        }

        return $this->options;
    }
}
