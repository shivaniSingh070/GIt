<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Log;

use Magento\Framework\Data\OptionSourceInterface;

class TypeImport implements OptionSourceInterface
{
    const TYPE_IMPORT_ADMIN = 1;  
    const TYPE_IMPORT_CLI_SINGLE = 2; 
    const TYPE_IMPORT_CLI_BULK = 3; 

    protected $options = null;
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null == $this->options) {
            $this->options = [
                ['value' => self::TYPE_IMPORT_ADMIN, 'label' => __('Admin GUI Import')],
                ['value' => self::TYPE_IMPORT_CLI_SINGLE, 'label' => __('CLI Single Import')],
                ['value' => self::TYPE_IMPORT_CLI_BULK, 'label' => __('CLI Bulk Import')],
            ];
        }

        return $this->options;
    }
}
