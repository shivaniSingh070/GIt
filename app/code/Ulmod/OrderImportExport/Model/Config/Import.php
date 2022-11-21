<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Config;

use Ulmod\OrderImportExport\Model\AbstractConfig;
use Ulmod\OrderImportExport\Api\Data\ImportConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
        
class Import extends AbstractConfig implements ImportConfigInterface
{
    const XML_PATH       = 'orderimportexport/settings/import';
    const ADMIN_RESOURCE = 'Ulmod_OrderImportExport::import';
    
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
     * @return string|null
     */
    public function getDelimiter()
    {
        return $this->getData(self::DELIMITER);
    }
    
    /**
     * @param string $delimiter
     *
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
    public function getEnclosure()
    {
        $enclosure = $this->getData(self::ENCLOSURE);
        if ($enclosure === null || $enclosure === '') {
            $enclosure = ' ';
        }

        return $enclosure;
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
     * @return int
     */
    public function getImportOrderNumber()
    {
        return (int)$this->getData(
            self::IMPORT_ORDER_NUMBER
        );
    }

    /**
     * @param int|bool $bool
     * @return $this
     */
    public function setImportOrderNumber($bool)
    {
        $this->setData(
            self::IMPORT_ORDER_NUMBER,
            (int)$bool
        );

        return $this;
    }

    /**
     * @return int
     */
    public function getCreateInvoice()
    {
        return (int)$this->getData(self::CREATE_INVOICE);
    }

    /**
     * @param int|bool $bool
     * @return $this
     */
    public function setCreateInvoice($bool)
    {
        $this->setData(self::CREATE_INVOICE, (int)$bool);

        if (!$bool) {
            $this->setCreateCreditMemo(false);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getCreateShipment()
    {
        return (int)$this->getData(self::CREATE_SHIPMENT);
    }

    /**
     * @param int|bool $bool
     * @return $this
     */
    public function setCreateShipment($bool)
    {
        $this->setData(
            self::CREATE_SHIPMENT,
            (int)$bool
        );

        return $this;
    }
    
    /**
     * @return int
     */
    public function getCreateCreditMemo()
    {
        $result = (int)$this->getData(
            self::CREATE_CREDIT_MEMO
        );
        if (!$this->getCreateInvoice()) {
            $result = 0;
        }

        return $result;
    }

    /**
     * @param int|bool $bool
     *
     * @return $this
     */
    public function setCreateCreditMemo($bool)
    {
        $this->setData(
            self::CREATE_CREDIT_MEMO,
            (int)$bool
        );

        return $this;
    }
    
    /**
     * @return int
     */
    public function getErrorLimit()
    {
        return (int)$this->getData(self::ERROR_LIMIT);
    }

    /**
     * @param int $int
     * @return $this
     */
    public function setErrorLimit($int)
    {
        $this->setData(self::ERROR_LIMIT, (int)$int);

        return $this;
    }

    /**
     * @return $this
     */
    public function saveConfig()
    {
        $this->unsetData('file');

        return parent::saveConfig();
    }
}
