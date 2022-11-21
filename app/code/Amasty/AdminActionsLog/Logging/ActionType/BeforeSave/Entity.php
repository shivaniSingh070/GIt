<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\ActionType\BeforeSave;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandlerProvider;

class Entity implements LoggingActionInterface
{
    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var SaveHandlerProvider
     */
    private $entityTypeProvider;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    public function __construct(
        MetadataInterface $metadata,
        SaveHandlerProvider $entityTypeProvider,
        ObjectDataStorageInterface $dataStorage
    ) {
        $this->metadata = $metadata;
        $this->entityTypeProvider = $entityTypeProvider;
        $this->dataStorage = $dataStorage;
    }

    public function execute(): void
    {
        if ($loggingObject = $this->metadata->getObject()) {
            $storageKey = spl_object_id($loggingObject) . '.before';
            if ($this->dataStorage->isExists($storageKey)) {
                return;
            }

            $entityLogType = $this->entityTypeProvider->get(get_class($loggingObject));
            if ($beforeData = $entityLogType->processBeforeSave($loggingObject)) {
                $this->dataStorage->set($storageKey, $beforeData);
            }
        }
    }
}
