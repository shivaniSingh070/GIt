<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Model;

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State as AppState;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * Path to store config
     *
     * @var string|int
     */
    const XML_PATH_LOGS_EXPORT = 'umorderimportexport/logs/export';
    const XML_PATH_LOGS_IMPORT = 'umorderimportexport/logs/import';
    const XML_PATH_CLEAR_EXPORT_LOG = 'umorderimportexport/clear/export';
    const XML_PATH_CLEAR_IMPORT_LOG = 'umorderimportexport/clear/import';
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param AppState $appState
     * @param Registry $registry
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AppState $appState,
        ScopeConfigInterface $scopeConfig,
        Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
    }
    
    /**
     * Get current store
     *
     * @return string
     */
    public function getCurrentStore()
    {
        $store = $this->storeManager->getStore();

        if ($this->appState->getAreaCode() == FrontNameResolver::AREA_CODE) {
            /** @var \Magento\Sales\Model\Order $order */
            if ($order = $this->registry->registry('current_order')) {
                return $order->getStoreId();
            }
            
            return 0;
        }
        return $store->getId();
    }

    /**
     * Is export logs enabled?
     *
     * @return bool
     */
    public function isExportLogEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOGS_EXPORT,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Is import logs enabled?
     *
     * @return bool
     */
    public function isImportLogEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOGS_IMPORT,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get clear export log days
     *
     * @return int
     */
    public function getClearExportOrderLogDays()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CLEAR_EXPORT_LOG,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get clear import log days
     *
     * @return int
     */
    public function getClearImportOrderLogDays()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CLEAR_IMPORT_LOG,
            ScopeInterface::SCOPE_STORE
        );
    }
}
