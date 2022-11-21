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

class InvoiceObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\CustomOrderNumber\Helper\Data
     */
    protected $helper;

    /**
     * Invoice Interface
     *
     * @var \Magento\Sales\Api\Data\InvoiceInterface
     */
    protected $invoice;

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
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @param \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
     */

    public function __construct(
        \Bss\CustomOrderNumber\Helper\Data $helper,
        \Magento\Sales\Api\Data\InvoiceInterface $invoice,
        \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
    ) {
        $this->helper = $helper;
        $this->invoice = $invoice;
        $this->sequence = $sequence;
    }

    /**
     * Set Increment Id
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        $invoiceInstance = $observer->getInvoice();
        if ($invoiceInstance->getId()) {
            return;
        }

        $storeId = $invoiceInstance->getOrder()->getStoreId();
        try {
            if ($invoiceInstance->getId() || $invoiceInstance->getId() !== null) {
                return;
            }
        } catch (\Exception $e) {
        }
        if ($this->helper->isInvoiceEnable($storeId)) {
            $entityType = 'invoice';
            if ($this->helper->isInvoiceSameOrder($storeId)) {
                $orderIncrement = $invoiceInstance->getOrder()->getIncrementId();
                $replace = $this->helper->getInvoiceReplace($storeId);
                $replaceWith = $this->helper->getInvoiceReplaceWith($storeId);
                $result = str_replace($replace, $replaceWith, $orderIncrement);
            } else {
                $format = $this->helper->getInvoiceFormat($storeId);
                $startValue = $this->helper->getInvoiceStart($storeId);
                $step = $this->helper->getInvoiceIncrement($storeId);
                $padding = $this->helper->getInvoicePadding($storeId);
                $pattern = "%0" . $padding . "d";

                $table = $this->resultTable($entityType, $storeId);

                $counter = $this->sequence->counterPlusOne($table, $startValue, $step, $pattern);
                $result = $this->sequence->replace($format, $storeId, $counter, $padding);
            }
            if (strpos($invoiceInstance->getIncrementId(), $result) === false) {
                try {
                    $unique = $this->sequence->checkUniqueInvoice($result, $storeId);
                    if ($unique == '0') {
                        $result = $this->resultIncrementId($result, $storeId);
                    }
                } catch (\Exception $e) {
                }

                $invoiceInstance->setIncrementId($result);
            }
        } else {
            $this->checkDisableInvoice($storeId, $invoiceInstance);
        }
    }

    /**
     * @param $storeId
     * @param $invoiceInstance
     */
    private function checkDisableInvoice($storeId, $invoiceInstance)
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
        $unique = $this->sequence->checkUniqueInvoice($result, $storeId);
        if ($unique == '0') {
            $i = 1;
            $check = $result;
            do {
                $unique = $this->sequence->checkUniqueInvoice($check, $storeId);
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
        $invoiceInstance->setIncrementId($result);
    }

    /**
     * @param $result
     * @param $storeId
     * @return string
     */
    private function resultIncrementId($result, $storeId)
    {
        $i = 1;
        $check = $result;
        do {
            $unique = $this->sequence->checkUniqueInvoice($check, $storeId);
            if ($unique == '0') {
                $check = $result . '-' . $i;
                $i++;
            }
            if ($unique == '1') {
                $result = $check;
            }
        } while ($unique == '0');

        return $result;
    }

    /**
     * @param $entityType
     * @param $storeId
     * @return string
     */
    private function resultTable($entityType, $storeId)
    {
        if ($this->helper->isIndividualInvoiceEnable($storeId)) {
            $table = $this->sequence->getSequenceTable($entityType, $storeId);
        } else {
            $table = $this->sequence->getSequenceTable($entityType, '0');
        }
        return $table;
    }
}
