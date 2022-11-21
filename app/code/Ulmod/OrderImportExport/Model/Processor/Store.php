<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Ulmod\OrderImportExport\Model\Cache as ModelCache;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\Data\StoreInterface;
        
class Store extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var ModelCache
     */
    private $cache;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreRepositoryInterface
     */
    private $repository;

    /**
     * @param ModelCache $cache
     * @param StoreRepositoryInterface $repository
     * @param StoreManagerInterface  $storeManager
     */
    public function __construct(
        ModelCache $cache,
        StoreRepositoryInterface $repository,
        StoreManagerInterface $storeManager,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->cache = $cache;
        $this->repository = $repository;
        $this->storeManager = $storeManager;
    }

    /**
     * Set store
     *
     * @param array $data
     * @param OrderInterface|\Magento\Sales\Model\Order $order
     * @return $this
     */
    public function process(array $data, OrderInterface $order)
    {
        if (isset($data['store_code'])) {
            $store = $this->getByCode(
                $data['store_code']
            );
        } elseif (isset($data['store_id'])) {
            $store = $this->getById(
                $data['store_id']
            );
        } else {
            $store = $this->storeManager->getDefaultStoreView();
        }

        if ($store !== false && $store !== null) {
            $order->setStore(
                $store
            );
            $order->setStoreId(
                $store->getId()
            );
            $order->setStoreName(
                $store->getName()
            );
        }

        return $this;
    }

    /**
     * Get store by code
     *
     * @param string $code
     * @return bool|StoreInterface|\Magento\Store\Model\Store
     */
    private function getByCode($code)
    {
        try {
            /** @var StoreInterface|\Magento\Store\Model\Store $storeObject */
            $storeObject = $this->cache->getStore($code);
            
            if ($storeObject === false) {
                $storeObject = $this->repository->get($code);
                $this->cache->addStore(
                    $code,
                    $storeObject
                );
                
                $this->cache->addStore(
                    $storeObject->getId(),
                    $storeObject
                );

                $website = $storeObject->getWebsite();
                
                $this->cache->addWebsite(
                    $website->getId(),
                    $website
                );
                $this->cache->addWebsite(
                    $website->getCode(),
                    $website
                );
            }
        } catch (\Exception $e) {
            $storeObject = $this->getById(
                $this->storeManager->getDefaultStoreView()
                    ->getId()
            );
        }

        return $storeObject;
    }

    /**
     * Get store by id
     *
     * @param int $id
     * @return bool|StoreInterface|\Magento\Store\Model\Store
     */
    private function getById($id)
    {
        try {
            /** @var StoreInterface|\Magento\Store\Model\Store $storeObject */
            $storeObject = $this->cache->getStore($id);
            if ($storeObject === false) {
                $storeObject = $this->repository->getById($id);
                
                $this->cache->addStore(
                    $id,
                    $storeObject
                );
                $this->cache->addStore(
                    $storeObject->getCode(),
                    $storeObject
                );

                $website = $storeObject->getWebsite();
                
                $this->cache->addWebsite(
                    $website->getId(),
                    $website
                );
                $this->cache->addWebsite(
                    $website->getCode(),
                    $website
                );
            }
        } catch (\Exception $e) {
            $storeObject = $this->getById(
                $this->storeManager->getDefaultStoreView()
                    ->getId()
            );
        }

        return $storeObject;
    }
}
