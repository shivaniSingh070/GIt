<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler;

use Amasty\AdminActionsLog\Api\Data\LogDetailInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Api\Restoring\EntityRestoreHandlerInterface;
use Magento\Framework\ObjectManagerInterface;

abstract class AbstractHandler implements EntityRestoreHandlerInterface
{
    const STORAGE_CODE_PREFIX = 'action_type';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    /**
     * @var array
     */
    private $modelObjects = [];

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectDataStorageInterface $dataStorage
    ) {
        $this->objectManager = $objectManager;
        $this->dataStorage = $dataStorage;
    }

    protected function getModelObject(LogEntryInterface $logEntry, LogDetailInterface $logDetail)
    {
        $elementId = $logEntry->getElementId();
        $modelName = $logDetail->getModel();

        return $this->objectManager->create($modelName)->load($elementId);
    }

    protected function setRestoreActionFlag($object): void
    {
        $storageKey = spl_object_id($object) . '.' . self::STORAGE_CODE_PREFIX;
        $this->dataStorage->set($storageKey, []);
    }
}
