<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Analytics;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Context;
use Amasty\Mostviewed\Model\Analytics\ViewFactory;
use Amasty\Mostviewed\Api\ViewRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class View
 * @package Amasty\Mostviewed\Controller\Analytics
 */
class View extends Ctr
{
    public function __construct(
        ViewFactory $tempFactory,
        ViewRepositoryInterface $dataRepository,
        SessionManagerInterface $sessionManager,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($tempFactory, $dataRepository, $sessionManager, 'block_id', $logger, $context);
    }
}
