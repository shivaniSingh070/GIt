<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Filter;

use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Sales\Creditmemo;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Sales\Invoice;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Sales\Order;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Sales\Shipment;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionModifierInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

class FilterOrderListing implements CollectionModifierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    private $subEntityConditions = [
        [
            'alias' => 'order_table',
            'table' => 'sales_order',
            'join_field' => OrderInterface::ENTITY_ID,
            'entity_field' => OrderInterface::ENTITY_ID,
            'category' => Order::CATEGORY
        ],
        [
            'alias' => 'invoice_table',
            'table' => 'sales_invoice',
            'join_field' => InvoiceInterface::ENTITY_ID,
            'entity_field' => InvoiceInterface::ORDER_ID,
            'category' => Invoice::CATEGORY
        ],
        [
            'alias' => 'shipment_table',
            'table' => 'sales_shipment',
            'join_field' => ShipmentInterface::ENTITY_ID,
            'entity_field' => ShipmentInterface::ORDER_ID,
            'category' => Shipment::CATEGORY
        ],
        [
            'alias' => 'creditmemo_table',
            'table' => 'sales_creditmemo',
            'join_field' => CreditmemoInterface::ENTITY_ID,
            'entity_field' => CreditmemoInterface::ORDER_ID,
            'category' => Creditmemo::CATEGORY
        ]
    ];

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function apply(AbstractDb $collection)
    {
        $sensitiveFields = [];
        $orderId = (int)$this->request->getParam('order_id');
        if (!$orderId) {
            return;
        }

        foreach ($this->subEntityConditions as $conditionData) {
            $sensitiveFields[] = $conditionData['alias'] . '.' . $conditionData['join_field'];
            $collection->getSelect()->joinLeft(
                [$conditionData['alias'] => $collection->getTable($conditionData['table'])],
                sprintf(
                    'main_table.%s = %s.%s AND main_table.category = \'%s\' AND %s.%s = %s',
                    LogEntry::ELEMENT_ID,
                    $conditionData['alias'],
                    $conditionData['join_field'],
                    $conditionData['category'],
                    $conditionData['alias'],
                    $conditionData['entity_field'],
                    $orderId
                ),
                []
            );
        }

        $collection->getSelect()->where(
            new \Zend_Db_Expr(
                sprintf('COALESCE (%s) IS NOT NULL', implode(',', $sensitiveFields))
            )
        );
    }
}
