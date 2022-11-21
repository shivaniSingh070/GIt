<?php

namespace Amasty\ImportCore\Api;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Magento\Framework\App\RequestInterface;

interface FormInterface
{
    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array;

    public function getData(ProfileConfigInterface $profileConfig): array;

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface;
}
