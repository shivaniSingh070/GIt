<?php
/*** Copyright © Ulmod. All rights reserved. **/

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Ulmod\OrderImportExport\Api\ImporterInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Framework\Exception\LocalizedException;
        
class CreditMemo extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var CreditmemoService
     */
    private $service;

    /**
     * @var CreditmemoFactory
     */
    private $creditmemoFactory;

    /**
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoManagementInterface $service
     * @param array $excludedFields
     */
    public function __construct(
        CreditmemoFactory $creditmemoFactory,
        CreditmemoManagementInterface $service,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->creditmemoFactory = $creditmemoFactory;
        $this->service = $service;
    }

    /**
     * @param array $data
     * @param OrderInterface $order
     * @return $this|mixed
     * @throws LocalizedException
     */
    public function process(array $data, OrderInterface $order)
    {
        $creditmemoData = [];

        $keys = [
            CreditmemoInterface::TAX_AMOUNT => OrderInterface::TAX_REFUNDED,
            CreditmemoInterface::BASE_TAX_AMOUNT  => OrderInterface::BASE_TAX_REFUNDED,
            CreditmemoInterface::DISCOUNT_AMOUNT => OrderInterface::DISCOUNT_REFUNDED,
            CreditmemoInterface::BASE_DISCOUNT_AMOUNT => OrderInterface::BASE_DISCOUNT_REFUNDED,
            CreditmemoInterface::SHIPPING_TAX_AMOUNT => OrderInterface::SHIPPING_TAX_REFUNDED,
            CreditmemoInterface::BASE_SHIPPING_TAX_AMOUNT => OrderInterface::BASE_SHIPPING_TAX_REFUNDED,
            CreditmemoInterface::SHIPPING_AMOUNT  => OrderInterface::SHIPPING_REFUNDED,
            CreditmemoInterface::BASE_SHIPPING_AMOUNT => OrderInterface::BASE_SHIPPING_REFUNDED,
            CreditmemoInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT => OrderInterface::DISCOUNT_TAX_COMPENSATION_REFUNDED,
            CreditmemoInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT => OrderInterface::BASE_DISCOUNT_TAX_COMPENSATION_REFUNDED,
            CreditmemoInterface::ADJUSTMENT_NEGATIVE => OrderInterface::ADJUSTMENT_NEGATIVE,
            CreditmemoInterface::BASE_ADJUSTMENT_NEGATIVE => OrderInterface::BASE_ADJUSTMENT_NEGATIVE,
            CreditmemoInterface::ADJUSTMENT_POSITIVE => OrderInterface::ADJUSTMENT_POSITIVE,
            CreditmemoInterface::BASE_ADJUSTMENT_POSITIVE => OrderInterface::BASE_ADJUSTMENT_POSITIVE,
            CreditmemoInterface::SUBTOTAL => OrderInterface::SUBTOTAL_REFUNDED,
            CreditmemoInterface::SUBTOTAL => OrderInterface::BASE_SUBTOTAL_REFUNDED,
            CreditmemoInterface::BASE_GRAND_TOTAL => OrderInterface::BASE_TOTAL_REFUNDED,
            CreditmemoInterface::GRAND_TOTAL => OrderInterface::TOTAL_REFUNDED
        ];

        $isCreateCreditMemo = $this->getConfig()->getCreateCreditMemo();
        $creditMemoSize = $order->getCreditmemosCollection()->getSize();
        if ($isCreateCreditMemo && !$creditMemoSize && $order->getInvoice()) {
            $this->removeExcludedFields($data);
            $products = $data[ImporterInterface::KEY_PRODUCTS_ORDERED];

            /** @var OrderItemInterface|\Magento\Sales\Model\Order\Item $item */
            foreach ($order->getAllItems() as $item) {
                $keyData = $item->getData('key');
                $parentKeyData = $item->getData('parent_key');

                if ($parentKeyData && isset($products[$parentKeyData]['bundle_items'][$keyData][OrderItemInterface::QTY_REFUNDED])) {
                    $quantity = $products[$parentKeyData]['bundle_items'][$keyData][OrderItemInterface::QTY_REFUNDED];
                } elseif (isset($products[$keyData][OrderItemInterface::QTY_REFUNDED])) {
                    $quantity = $products[$keyData][OrderItemInterface::QTY_REFUNDED];
                } else {
                    $quantity = $item->getQtyToRefund();
                }

                $quantity = (float)$quantity;
                if (!$quantity) {
                    continue;
                }

                $creditmemoData['qtys'][$item->getId()] = $quantity;
            }

            foreach ($keys as $creditmemoKey => $orderKey) {
                if (isset($data[$orderKey])) {
                    $creditmemoData[$creditmemoKey] = (float)$data[$orderKey];
                }
            }
        }

        if ($creditmemoData) {
            /** @var CreditmemoInterface|\Magento\Sales\Model\Order\Creditmemo $creditmemo */
            $creditmemo = $this->creditmemoFactory->createByOrder(
                $order,
                $creditmemoData
            );
            $creditmemo->setState($creditmemo::STATE_REFUNDED);
            $creditmemo->setSendEmail(false);

            foreach ($keys as $creditmemoKey => $orderKey) {
                if (isset($data[$orderKey])) {
                    $creditmemo->setData(
                        $creditmemoKey,
                        (float)$data[$orderKey]
                    );
                }
            }

            $this->service->refund($creditmemo);
        }

        return $this;
    }
}
