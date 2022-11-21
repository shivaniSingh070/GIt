<?php
declare(strict_types=1);

namespace Amasty\GiftCardSmsNotifications\Model\Smspro;

use Magento\Framework\ObjectManagerInterface;

class ApicallFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $className = \Magecomp\Smspro\Helper\Apicall::class)
    {
        return $this->objectManager->create($className);
    }
}
