<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data;

interface SanitizerInterface
{
    /**
     * @param string $value
     * @return string
     */
    public function sanitize($value);
}
