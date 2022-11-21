<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Api\Data;

interface ExportConfigInterface
{
    /**
     * Constants for keys of data.
     */
    const DIRECTORY = 'directory';
    const FILENAME  = 'filename';
    const ENCLOSURE = 'enclosure';
    const DELIMITER = 'delimiter';
    const FROM      = 'from';
    const TO        = 'to';

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename);

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $directory
     *
     * @return $this
     */
    public function setDirectory($directory);

    /**
     * @return string
     */
    public function getDirectory();
    
    /**
     * @param string $delimiter
     * @return $this
     */
    public function setDelimiter($delimiter);

    /**
     * @return string
     */
    public function getDelimiter();

    /**
     * @param string|null $date
     * @return $this
     */
    public function setFrom($date);

    /**
     * @return string
     */
    public function getFrom();

    /**
     * @param string $enclosure
     * @return $this
     */
    public function setEnclosure($enclosure);

    /**
     * @return string
     */
    public function getEnclosure();

    /**
     * @param string|null $date
     * @return $this
     */
    public function setTo($date);

    /**
     * @return string
     */
    public function getTo();
}
