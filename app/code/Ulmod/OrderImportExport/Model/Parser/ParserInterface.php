<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Parser;

interface ParserInterface
{
    /**
     * @param string $value
     * @return array
     */
    public function parse($value);
}
