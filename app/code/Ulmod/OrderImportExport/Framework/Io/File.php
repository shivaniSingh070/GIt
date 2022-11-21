<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Framework\Io;

use Magento\Framework\Filesystem\Io\File as IoFile;

class File extends IoFile
{
   /**
    * Unset stream
    *
    * @return $this
    */
    public function unsetStream()
    {
        $this->streamClose();
        $this->_streamFileName = null;
        $this->_streamHandler  = null;

        return $this;
    }

    /**
     * Set stream
     *
     * @param string $file
     * @param string $mode
     * @return $this
     */
    public function setStream($file, $mode = 'r+')
    {
        $this->_streamFileName = $file;
        $this->_streamChmod    = 0660;
        $this->_streamHandler  = fopen($file, $mode);

        return $this;
    }
}
