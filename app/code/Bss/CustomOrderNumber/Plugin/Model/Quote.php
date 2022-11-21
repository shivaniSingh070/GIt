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

namespace Bss\CustomOrderNumber\Plugin\Model;

class Quote
{
    /**
     * @var \Bss\CustomOrderNumber\Helper\Data
     */
    private $helper;

    /**
     * Sequence
     *
     * @var \Bss\CustomOrderNumber\Model\ResourceModel\Sequence
     */
    private $sequence;

    /**
     * Quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $session;

    /**
     * Quote Model
     *
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $quoteResourceModel;

    /**
     * @var \Magento\Sales\Model\OrderIncrementIdChecker
     */
    private $orderIncrementIdChecker;

    /**
     * Sequence constructor.
     * @param \Bss\CustomOrderNumber\Helper\Data $helper
     * @param \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
     * @param \Magento\Backend\Model\Session\Quote $session
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResourceModel
     * @param \Magento\Sales\Model\OrderIncrementIdChecker|null $orderIncrementIdChecker
     */
    public function __construct(
        \Bss\CustomOrderNumber\Helper\Data $helper,
        \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence,
        \Magento\Backend\Model\Session\Quote $session,
        \Magento\Quote\Model\ResourceModel\Quote $quoteResourceModel,
        \Magento\Sales\Model\OrderIncrementIdChecker $orderIncrementIdChecker
    ) {

        $this->helper = $helper;
        $this->sequence = $sequence;
        $this->session = $session;
        $this->quoteResourceModel = $quoteResourceModel;
        $this->orderIncrementIdChecker = $orderIncrementIdChecker;
    }

    /**
     * Generate new increment order id and associate it with current quote plugin around
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param callable $proceed
     * @return $this
     */
    public function aroundReserveOrderId(\Magento\Quote\Model\Quote $quote, callable $proceed)
    {
        $storeId = $quote->getStoreId();
        if ($this->helper->isOrderEnable($storeId)) {
            $reservedOrderId = false;
            if (!$quote->getReservedOrderId()) {
                $originReservedOrderId = $this->quoteResourceModel->getReservedOrderId($quote);
                $reservedOrderId = $this->getReservedOrderIdNew($quote, $originReservedOrderId);
            } else {
                if ($this->orderIncrementIdChecker->isIncrementIdUsed($quote->getReservedOrderId())) {
                    $originReservedOrderId = $this->quoteResourceModel->getReservedOrderId($quote);
                    $reservedOrderId = $this->getReservedOrderIdNew($quote, $originReservedOrderId);
                }
            }
            if ($reservedOrderId) {
                $quote->setReservedOrderId($reservedOrderId);
            }
            return $this;
        } else {
            return $proceed();
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param string|null $originReservedOrderId
     * @return mixed
     */
    private function getReservedOrderIdNew($quote, $originReservedOrderId = null)
    {
        if ($originReservedOrderId) {
            $storeId = $quote->getStoreId();
            $sessionId = $this->session->getStoreId($quote);
            if (isset($sessionId)) {
                $storeId = $sessionId;
            }
            $format = $this->helper->getOrderFormat($storeId);
            $startValue = $this->helper->getOrderStart($storeId);
            $step = $this->helper->getOrderIncrement($storeId);
            $padding = $this->helper->getOrderPadding($storeId);
            $pattern = "%0".$padding."d";
            $entityType = 'order';
            if ($this->helper->isIndividualOrderEnable($storeId)) {
                $table = $this->sequence->getSequenceTable($entityType, $storeId);
            } else {
                $table = $this->sequence->getSequenceTable($entityType, '0');
            }

            $counter = $this->setCounter($storeId, $table, $startValue, $step, $pattern);
            $result = $this->sequence->replace($format, $storeId, $counter);
            $unique = $this->sequence->checkUnique($result, $storeId);
            if ($unique == '0') {
                $i = 1;
                $check = $result;
                do {
                    $unique = $this->sequence->checkUnique($check, $storeId);
                    if ($unique == '0') {
                        $check = $result.'-'.$i;
                        $i++;
                    }
                    if ($unique == '1') {
                        $result = $check;
                    }
                } while ($unique == '0');
            }
            return $result;
        }
    }

    /**
     * @param $storeId
     * @param $table
     * @param $startValue
     * @param $step
     * @param $pattern
     * @return int
     */
    private function setCounter($storeId, $table, $startValue, $step, $pattern)
    {
        if ($this->helper->isIndividualOrderEnable($storeId)) {
            return $this->sequence->counter($table, $startValue, $step, $pattern);
        }
        return $this->sequence->counterPlusOne($table, $startValue, $step, $pattern);
    }
}
