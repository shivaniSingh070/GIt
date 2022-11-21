<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Processor;

use Ulmod\OrderImportExport\Api\Data\ImportConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

interface ProcessorInterface
{
    /**
     * @param array $data
     * @param OrderInterface|Order $order
     * @return mixed
     */
    public function process(array $data, OrderInterface $order);

    /**
     * @param ImportConfigInterface $config
     * @return $this
     */
    public function setConfig(ImportConfigInterface $config);
}
