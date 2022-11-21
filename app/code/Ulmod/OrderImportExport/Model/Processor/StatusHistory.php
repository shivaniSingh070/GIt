<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Model\Processor\AbstractProcessor;
use Ulmod\OrderImportExport\Model\Processor\ProcessorInterface;
use Ulmod\OrderImportExport\Model\Parser\ParserInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
        
class StatusHistory extends AbstractProcessor implements ProcessorInterface
{
    const KEY = 'status_histories';

    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    private $objectFactory;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @param ParserInterface $parser
     * @param OrderStatusHistoryInterfaceFactory $objectFactory
     * @param array $excludedFields
     */
    public function __construct(
        ParserInterface $parser,
        OrderStatusHistoryInterfaceFactory $objectFactory,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->parser        = $parser;
        $this->objectFactory = $objectFactory;
    }

    /**
     * Process status history
     *
     * @param array $data
     * @param OrderInterface $order
     * @return $this
     */
    public function process(array $data, OrderInterface $order)
    {
        $statusData = $order->getData(self::KEY);
        $statuses = $this->parser->parse($statusData);

        foreach ($statuses as &$status) {
            /** @var OrderStatusHistoryInterface $object */
            $object = $this->objectFactory->create();
            $object->addData($status);
            $status = $object;
        }

        if ($statuses) {
            $order->setStatusHistories(
                $statuses
            );
        }

        return $this;
    }
}
