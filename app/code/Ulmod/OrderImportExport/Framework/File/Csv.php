<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Framework\File;

use Magento\Framework\Exception\LocalizedException;
use Ulmod\OrderImportExport\Framework\Io\File as IoFile;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Magento\Framework\File\Csv as CsvFile;
        
class Csv extends CsvFile
{
    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     * @param IoFile $ioFile
     * @param DriverFile $file
     */
    public function __construct(
        IoFile $ioFile,
        DriverFile $file
    ) {
        parent::__construct($file);
        $this->ioFile = $ioFile;
    }

    /**
     * @param string $enclosure
     *
     * @return bool
     */
    public function validateEnclosure($enclosure)
    {
        return $enclosure === null || $enclosure === ''
            || strlen($enclosure) === 1;
    }

    /**
     * @param string|null $delimiter
     *
     * @return bool
     */
    public function validateDelimiter($delimiter)
    {
        if ($delimiter == '\t') {
            $delimiter = "\t";
        }

        return $delimiter === null ||
               $delimiter === '' ||
               strlen($delimiter) === 1 ||
               $delimiter === "\t";
    }
    
    /**
     * @param string $enclosure
     * @return CsvFile
     * @throws LocalizedException
     */
    public function setEnclosure($enclosure)
    {
        if (!$this->validateEnclosure($enclosure)) {
            throw new LocalizedException(
                __('CSV field enclosures can only be one character in length.')
            );
        }

        return parent::setEnclosure($enclosure);
    }
    
    /**
     * @param string $delimiter
     * @return CsvFile
     * @throws LocalizedException
     */
    public function setDelimiter($delimiter)
    {
        if (!$this->validateDelimiter($delimiter)) {
            throw new LocalizedException(
                __('CSV delimiters can only be one character in length unless using \t for "tab".')
            );
        }

        return parent::setDelimiter($delimiter);
    }

    /**
     * @return $this
     */
    public function unsetStream()
    {
        $this->ioFile->unsetStream();

        return $this;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setStream($file)
    {
        $this->ioFile->setStream($file);

        return $this;
    }
    
    /**
     * @return $this
     */
    public function streamUnlock()
    {
        $this->ioFile->streamUnlock();

        return $this;
    }

    /**
     * @param bool $exclusive
     * @return $this
     */
    public function streamLock($exclusive = true)
    {
        $this->ioFile->streamLock($exclusive);

        return $this;
    }

    /**
     * @return false|array
     */
    public function streamReadCsv()
    {
        return $this->ioFile->streamReadCsv(
            $this->_delimiter,
            $this->_enclosure
        );
    }
}
