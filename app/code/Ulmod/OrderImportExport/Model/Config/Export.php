<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Config;

use Ulmod\OrderImportExport\Model\AbstractConfig;
use Ulmod\OrderImportExport\Api\Data\ExportConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
        
class Export extends AbstractConfig implements ExportConfigInterface
{
    const XML_PATH       = 'orderimportexport/settings/export';
    const ADMIN_RESOURCE = 'Ulmod_OrderImportExport::export';
    
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConfig $resourceConfig
     * @param Context $context
     * @param Registry $registry
     * @param string $xmlPath
     * @param array $defaultOptions
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConfig $resourceConfig,
        Context $context,
        Registry $registry,
        $xmlPath = self::XML_PATH,
        $defaultOptions = [],
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $resourceConfig,
            $context,
            $registry,
            $xmlPath,
            $defaultOptions,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->getData(self::DIRECTORY);
    }

    /**
     * @param string $directory
     * @return $this
     */
    public function setDirectory($directory)
    {
        $this->setData(self::DIRECTORY, $directory);

        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->setData(self::FILENAME, $filename);

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->getData(self::FILENAME);
    }

    /**
     * @param string $enclosure
     * @return $this
     */
    public function setEnclosure($enclosure)
    {
        $this->setData(self::ENCLOSURE, $enclosure);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEnclosure()
    {
        $enclosure = $this->getData(self::ENCLOSURE);
        if ($enclosure === null || $enclosure === '') {
            $enclosure = ' ';
        }

        return $enclosure;
    }

    /**
     * @param string $delimiter
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        $this->setData(self::DELIMITER, $delimiter);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDelimiter()
    {
        return $this->getData(self::DELIMITER);
    }
    
    /**
     * @param string|null $date
     * @return $this
     */
    public function setTo($date)
    {
        $this->setData(self::TO, $date);

        return $this;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->getData(self::TO);
    }


    /**
     * @param string|null $date
     * @return $this
     */
    public function setFrom($date)
    {
        $this->setData(self::FROM, $date);

        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->getData(self::FROM);
    }
}
