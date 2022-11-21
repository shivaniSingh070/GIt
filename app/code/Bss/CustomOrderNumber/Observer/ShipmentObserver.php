<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOrderNumber
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOrderNumber\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ShipmentObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\CustomOrderNumber\Helper\Data
     */
    protected $helper;

    /**
     * Shipment Interface
     *
     * @var \Magento\Sales\Api\Data\ShipmentInterface
     */
    protected $shipment;

    /**
     * Sequence
     *
     * @var \Bss\CustomOrderNumber\Model\ResourceModel\Sequence
     */
    protected $sequence;

    /**
     * Construct
     *
     * @param \Bss\CustomOrderNumber\Helper\Data $helper
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
     */
    public function __construct(
        \Bss\CustomOrderNumber\Helper\Data $helper,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment,
        \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
    ) {
        $this->helper = $helper;
        $this->shipment = $shipment;
        $this->sequence = $sequence;
    }

    /**
     * Set Increment Id
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $shipmentInstance = $observer->getShipment();
        if ($shipmentInstance->getId()) {
            return;
        }
        $storeId = $shipmentInstance->getOrder()->getStoreId();
        if ($this->helper->isShipmentEnable($storeId)) {
            $result = $this->getCustomIncrementId($storeId, $shipmentInstance);
            if ($result) {
                $shipmentInstance->setIncrementId($result);
            }
        } else {
            $this->checkDisableCredit($storeId, $shipmentInstance);
        }
    }

    /**
     * @param $storeId
     * @param $shipmentInstance
     * @return bool|mixed|string
     */
    private function getCustomIncrementId($storeId, $shipmentInstance)
    {
        $entityType = 'shipment';
        if ($this->helper->isShipmentSameOrder($storeId)) {
            $orderIncrement = $shipmentInstance->getOrder()->getIncrementId();
            $replace = $this->helper->getShipmentReplace($storeId);
            $replaceWith = $this->helper->getShipmentReplaceWith($storeId);
            $result = str_replace($replace, $replaceWith, $orderIncrement);
        } else {
            $format = $this->helper->getShipmentFormat($storeId);
            $startValue = $this->helper->getShipmentStart($storeId);
            $step = $this->helper->getShipmentIncrement($storeId);
            $padding = $this->helper->getShipmentPadding($storeId);
            $pattern = "%0" . $padding . "d";

            if ($this->helper->isIndividualShipmentEnable($storeId)) {
                $table = $this->sequence->getSequenceTable($entityType, $storeId);
            } else {
                $table = $this->sequence->getSequenceTable($entityType, '0');
            }

            $counter = $this->sequence->counterPlusOne($table, $startValue, $step, $pattern);
            $result = $this->sequence->replace($format, $storeId, $counter);
        }
        try {
            $unique = $this->sequence->checkUniqueShipment($result, $storeId);
            if ($unique == '0') {
                $i = 1;
                $check = $result;
                do {
                    $unique = $this->sequence->checkUniqueShipment($check, $storeId);
                    if ($unique == '0') {
                        $check = $result . '-' . $i;
                        $i++;
                    }
                    if ($unique == '1') {
                        $result = $check;
                    }
                } while ($unique == '0');
            }
            return $result;
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * @param $storeId
     * @param $shipmentInstance
     */
    private function checkDisableCredit($storeId, $shipmentInstance)
    {
        $pre = $storeId;
        if ($storeId == 1) {
            $pre = "";
        }
        $suff = "";
        $result = 1;
        $result = sprintf(
            \Magento\SalesSequence\Model\Sequence::DEFAULT_PATTERN,
            $pre,
            $result,
            $suff
        );
        $unique = $this->sequence->checkUniqueShipment($result, $storeId);
        if ($unique == '0') {
            $i = 1;
            $check = $result;
            do {
                $unique = $this->sequence->checkUniqueShipment($check, $storeId);
                if ($unique == '0') {
                    $check = $result + $i;
                    $i++;
                }
                if ($unique == '1') {
                    $result = $check;
                }
            } while ($unique == '0');
        }
        $result = sprintf(
            \Magento\SalesSequence\Model\Sequence::DEFAULT_PATTERN,
            "",
            $result,
            ""
        );
        $shipmentInstance->setIncrementId($result);
    }
}
