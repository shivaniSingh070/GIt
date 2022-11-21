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

class OrderObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\CustomOrderNumber\Helper\Data
     */
    protected $helper;

    /**
     * StoreManager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $session;

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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Session\Quote $session
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
     */
    public function __construct(
        \Bss\CustomOrderNumber\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session\Quote $session,
        \Bss\CustomOrderNumber\Model\ResourceModel\Sequence $sequence
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->session = $session;
        $this->sequence = $sequence;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $sessionId = $this->session->getStoreId();
        $orderInstance = $observer->getOrder();
        if (isset($sessionId)) {
            $storeId = $sessionId;
        }
        if (!$this->helper->isOrderEnable($storeId)) {
            $this->checkDisableOrder($storeId, $orderInstance);
        }
    }

    /**
     * @param $storeId
     * @param $orderInstance
     */
    private function checkDisableOrder($storeId, $orderInstance)
    {
        if ($orderInstance->getId()) {
            return;
        }
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
        $unique = $this->sequence->checkUnique($result, $storeId);
        if ($unique == '0') {
            $i = 1;
            $check = $result;
            do {
                $unique = $this->sequence->checkUnique($check, $storeId);
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
        $orderInstance->setIncrementId($result);
    }
}
