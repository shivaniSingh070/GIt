<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Ulmod\OrderImportExport\Api\ImporterInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
        
class Shipment extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $repository;

    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;
    
    /**
     * @param ShipmentFactory $shipmentFactory
     * @param ShipmentRepositoryInterface  $repository
     * @param array $excludedFields
     */
    public function __construct(
        ShipmentFactory $shipmentFactory,
        ShipmentRepositoryInterface $repository,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->shipmentFactory = $shipmentFactory;
        $this->repository = $repository;
    }

    /**
     * Process shipment
     *
     * @param array $data
     * @param OrderInterface $order
     * @return $this|mixed
     * @throws LocalizedException
     */
    public function process(array $data, OrderInterface $order)
    {
        $isCreateShipment = $this->getConfig()->getCreateShipment();
        if ($isCreateShipment) {
            $this->removeExcludedFields($data);

            $quantities = [];

            $productsData = $data[ImporterInterface::KEY_PRODUCTS_ORDERED];

            /** @var OrderItemInterface|\Magento\Sales\Model\Order\Item $item */
            foreach ($order->getAllItems() as $item) {
                $keyData  = $item->getData('key');
                $parentKeyData = $item->getData('parent_key');

                if ($parentKeyData && isset($productsData[$parentKeyData]['bundle_items'][$keyData][OrderItemInterface::QTY_SHIPPED])) {
                    $quantity = $productsData[$parentKeyData]['bundle_items'][$keyData][OrderItemInterface::QTY_SHIPPED];
                } elseif (isset($productsData[$keyData][OrderItemInterface::QTY_SHIPPED])) {
                    $quantity = $productsData[$keyData][OrderItemInterface::QTY_SHIPPED];
                } else {
                    $quantity = $item->getQtyToShip();
                }

                $quantity = (float)$quantity;
                if (!$quantity) {
                    continue;
                }

                $quantities[$item->getId()] = $quantity;
            }

            if ($quantities) {
                /** @var ShipmentInterface|OrderShipment $shipment */
                $shipment = $this->shipmentFactory->create(
                    $order,
                    $quantities
                );
                
                $shipment->setSendEmail(false);
                $shipment->register();
                
                $this->repository->save($shipment);
            }
        }

        return $this;
    }
}
