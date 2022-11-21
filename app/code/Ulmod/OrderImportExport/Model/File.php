<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as FileIo;
        
class File
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var FileIo
     */
    private $fileIo;

    /**
     * @param FileIo $fileIo
     * @param DirectoryList $directoryList
     */
    public function __construct(
        DirectoryList $directoryList,
        FileIo $fileIo
    ) {
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
    }

    /**
     * Returns expected absolute path to file
     *
     * @param string $filepath
     * @return string
     */
    public function getAbsolutePath($filepath)
    {
        if (strpos($filepath, DIRECTORY_SEPARATOR) !== 0) {
            $filepath = $this->assembleFilepath([
                $this->directoryList->getRoot(),
                $filepath
            ]);
        }

        return $filepath;
    }

    /**
     * Returns assemble file path
     *
     * @param array  $parts
     * @param string $glue
     * @return string
     */
    public function assembleFilepath(
        array $parts,
        $absolute = true,
        $glue = DIRECTORY_SEPARATOR
    ) {
        $parts = array_map(function ($value) use ($glue) {
            return trim($value, $glue);
        }, $parts);

        $filepath = implode($glue, $parts);
        if ($absolute === true) {
            $filepath = $glue . $filepath;
        }

        return $filepath;
    }

    /**
     * Returns expected relative path to file
     *
     * @param string $filepath
     * @return string
     */
    public function getRelativePath($filepath)
    {
        $rootDir = $this->directoryList->getRoot();
        if (strpos($filepath, $rootDir) === 0) {
            $filepath = ltrim(
                substr($filepath, strlen($rootDir)),
                DIRECTORY_SEPARATOR
            );
        }

        return $filepath;
    }
    

    /**
     * Get file name
     *
     * @param string $filepath
     * @return string|null
     */
    public function getFilename($filepath)
    {
        $pathInfo = $this->fileIo->getPathInfo($filepath);

        return isset($pathInfo['filename'])
            ? $pathInfo['filename'] : null;
    }
    
    /**
     * Is file exists
     *
     * @param string $file
     * @param bool   $onlyFile
     * @return bool
     */
    public function fileExists(
        $file,
        $onlyFile = true
    ) {
        return $this->fileIo->fileExists(
            $file,
            $onlyFile
        );
    }

    /**
     * Get base name
     *
     * @param string $filepath
     * @return string|null
     */
    public function getBasename($filepath)
    {
        $pathInfo = $this->fileIo->getPathInfo($filepath);

        return isset($pathInfo['basename'])
            ? $pathInfo['basename'] : null;
    }

    /**
     * Get directory name
     *
     * @param string $filepath
     * @return string|null
     */
    public function getDirname($filepath)
    {
        $pathInfo = $this->fileIo->getPathInfo($filepath);

        return isset($pathInfo['dirname'])
            ? $pathInfo['dirname'] : null;
    }
    
    /**
     * Get extension
     *
     * @param string $filepath
     * @return string|null
     */
    public function getExtension($filepath)
    {
        $pathInfo = $this->fileIo->getPathInfo($filepath);

        return isset($pathInfo['extension'])
            ? $pathInfo['extension'] : null;
    }
}
