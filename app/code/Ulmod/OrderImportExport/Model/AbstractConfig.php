<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Model\Store as StoreModel;
        
abstract class AbstractConfig extends AbstractModel
{
    /**
     * @var ResourceConfig
     */
    private $resourceConfig;

    /**
     * @var string
     */
    private $xmlPath;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConfig $resourceConfig
     * @param Context $context
     * @param Registry $registry
     * @param string $xmlPath
     * @param array $defaultOptions
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConfig $resourceConfig,
        Context $context,
        Registry $registry,
        $xmlPath,
        $defaultOptions = [],
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->xmlPath        = $xmlPath;
        $this->resourceConfig = $resourceConfig;

        if (is_array($defaultOptions)) {
            $this->addData($defaultOptions);
        }

        $value = $scopeConfig->getValue(
            $this->xmlPath,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            StoreModel::DEFAULT_STORE_ID
        );

        $value = unserialize($value);
        if (is_array($value)) {
            $this->addData($value);
        }
    }

    /**
     * @return $this
     */
    public function saveConfig()
    {
        $value = serialize($this->getData());
        $this->resourceConfig->saveConfig(
            $this->xmlPath,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            StoreModel::DEFAULT_STORE_ID
        );

        return $this;
    }
}
