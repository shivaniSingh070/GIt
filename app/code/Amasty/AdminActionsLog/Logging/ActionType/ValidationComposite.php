<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\ActionType;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\ActionType\Validation\ActionValidatorInterface;

class ValidationComposite implements LoggingActionInterface
{
    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var LoggingActionInterface
     */
    private $wrappedAction;

    /**
     * @var ActionValidatorInterface[]
     */
    private $validators;

    public function __construct(
        MetadataInterface $metadata,
        LoggingActionInterface $wrappedAction,
        array $validators = []
    ) {
        $this->metadata = $metadata;
        $this->wrappedAction = $wrappedAction;
        $this->validators = $validators;
    }

    public function execute(): void
    {
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($this->metadata)) {
                return;
            }
        }

        $this->wrappedAction->execute();
    }
}
