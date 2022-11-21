<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Log;

use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class File
{
    const IMPORTED_HISTORY_DIR = 'ulmod_orderimport/file';
    const IMPORTED_RELATIVE_HISTORY_DIR = 'var/ulmod_orderimport/file';
    const EXPORTED_RELATIVE_HISTORY_DIR = 'var/ulmod_orderexport';    
    const EXPORTED_HISTORY_DIR = 'ulmod_orderexport/';
    
    /**
     * sub folder
     * @var string
     */
    protected $subImportedDir = 'ulmod_orderimport';

    /**
     * sub folder
     * @var string
     */
    protected $subExportedDir = 'ulmod_orderexport';
    

    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Get imported relative file path.
     *
     * @param string $filename
     * @return string
     */
    public function getImportedRelativeFilePath($filename)
    {
        return self::IMPORTED_RELATIVE_HISTORY_DIR . $filename;
    }
    
    /**
     * Get exported relative file path.
     *
     * @param string $filename
     * @return string
     */
    public function getExportedRelativeFilePath($filename)
    {
        return self::EXPORTED_RELATIVE_HISTORY_DIR . $filename;
    }

    /**
     * get base imported file dir
     *
     * @return string
     */
    public function getBaseImportedDir()
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath($this->subImportedDir . '/file');
    }

    /**
     * get base exported file dir
     *
     * @return string
     */
    public function getBaseExportedDir()
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath($this->subExportedDir . '/filename');
    }
}
