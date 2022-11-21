<?php

namespace Amasty\Pgrid\Observer;

use Amasty\Pgrid\Model\Indexer\QtySold;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var QtySold
     */
    private $qtySoldIndexer;

    public function __construct(QtySold $qtySoldIndexer)
    {
        $this->qtySoldIndexer = $qtySoldIndexer;
    }

    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getData('order');

        if ($order && $order->getEntityId()) {
            $order->getResource()->addCommitCallback(function () use ($order) {
                $this->qtySoldIndexer->executeRow((int)$order->getEntityId());
            });
        }
    }
}
