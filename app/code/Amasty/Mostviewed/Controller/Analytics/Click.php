<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Analytics;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Context;
use Amasty\Mostviewed\Model\Analytics\ClickFactory;
use Amasty\Mostviewed\Api\ClickRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Click
 * @package Amasty\Mostviewed\Controller\Analytics
 */
class Click extends Ctr
{
    public function __construct(
        ClickFactory $tempFactory,
        ClickRepositoryInterface $dataRepository,
        SessionManagerInterface $sessionManager,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($tempFactory, $dataRepository, $sessionManager, 'product_id', $logger, $context);
    }
}
