<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\ActionType\Validation;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\ConfigProvider;
use Magento\Framework\App\HttpRequestInterface;

class LoggingVisitHistoryValidator implements ActionValidatorInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var bool|null
     */
    private $validationResult = null;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function isValid(MetadataInterface $metadata): bool
    {
        if ($this->validationResult === null) {
            $request = $metadata->getRequest();

            $this->validationResult = $this->configProvider->isEnabledLogVisitHistory()
                && $request instanceof HttpRequestInterface
                && !$request->isAjax();
        }

        return $this->validationResult;
    }
}
