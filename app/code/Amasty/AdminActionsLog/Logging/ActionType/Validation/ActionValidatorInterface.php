<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\ActionType\Validation;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;

interface ActionValidatorInterface
{
    /**
     * Validate Action Metadata.
     *
     * @param MetadataInterface $metadata
     * @return bool
     */
    public function isValid(MetadataInterface $metadata): bool;
}
