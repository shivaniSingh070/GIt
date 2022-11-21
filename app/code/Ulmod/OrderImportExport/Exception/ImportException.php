<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Exception;

class ImportException extends \Exception
{
    /**
     * Import status
     */
    const IMPORT_STATUS_NO  = 0;
    const IMPORT_STATUS_YES = 1;

    /**
     * @var int
     */
    private $imported;

    /**
     * @param int $imported
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        $imported,
        $message = "",
        $code = 0,
        \Exception $previous = null
    ) {
        $this->imported = (int)$imported;
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }

    /**
     * @return bool
     */
    public function isNotImported()
    {
        return $this->imported === self::IMPORT_STATUS_NO;
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        return $this->imported === self::IMPORT_STATUS_YES;
    }
}
