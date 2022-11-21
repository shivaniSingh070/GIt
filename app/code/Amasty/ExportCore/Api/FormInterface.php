<?php

namespace Amasty\ExportCore\Api;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Magento\Framework\App\RequestInterface;

interface FormInterface
{
    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array;

    public function getData(ProfileConfigInterface $profileConfig): array;

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface;
}
