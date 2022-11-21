<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data\Validator;

use Ulmod\OrderImportExport\Model\Data\Validator\ResultInterface;

class Result implements ResultInterface
{
    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var array
     */
    private $invalidData;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @param bool  $isValid
     * @param null|string $message
     * @param array $invalidData
     */
    public function __construct(
        $isValid,
        $message = null,
        array $invalidData = []
    ) {
        $this->isValid     = $isValid;
        $this->message     = $message;
        $this->invalidData = $invalidData;
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return (bool)$this->isValid;
    }

    /**
     * @return array
     */
    public function getInvalidData()
    {
        return $this->invalidData;
    }
}
