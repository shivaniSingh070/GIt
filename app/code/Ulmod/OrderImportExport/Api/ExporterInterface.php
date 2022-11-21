<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Api;

interface ExporterInterface
{
    /**
     * Exports to csv file
     *
     * @param null|bool|int|float|array|object $args
     * @return string
     * @throws \Exception
     */
    public function export($args = null);
}
