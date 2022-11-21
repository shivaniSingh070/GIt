<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data;

use Ulmod\OrderImportExport\Model\Data\ValidatorInterface;
use Ulmod\OrderImportExport\Model\Data\Validator\ResultInterface;

class Validator
{
    /**
     * @var ValidatorInterface[]
     */
    private $pool;

    /**
     * @param ValidatorInterface[] $pool
     */
    public function __construct($pool = [])
    {
        $this->pool = $pool;
    }

    /**
     * @param array $data
     * @return bool|ResultInterface[]
     */
    public function validate(array $data)
    {
        $result = [];

        foreach ($this->pool as $validator) {
            $validateData = $validator->validate($data);
            if (!$validateData->isValid()) {
                $result[] = $validateData;
            }
        }

        return empty($result) ? true : $result;
    }
}
