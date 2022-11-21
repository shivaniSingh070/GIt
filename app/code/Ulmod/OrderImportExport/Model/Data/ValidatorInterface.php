<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data;

interface ValidatorInterface
{
    /**
     * @param array $data
     * @return bool|array
     */
    public function validate(array $data);
}
