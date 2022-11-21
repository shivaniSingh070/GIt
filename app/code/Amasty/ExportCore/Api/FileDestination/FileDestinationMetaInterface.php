<?php

namespace Amasty\ExportCore\Api\FileDestination;

use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Magento\Framework\App\RequestInterface;

interface FileDestinationMetaInterface
{
    public function getMeta(): array;

    public function prepareConfig(
        ProfileConfigInterface $profileConfig,
        RequestInterface $request
    ): FileDestinationMetaInterface;
}
