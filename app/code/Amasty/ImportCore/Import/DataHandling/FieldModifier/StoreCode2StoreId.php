<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\AbstractModifier;
use Amasty\ImportCore\Import\DataHandling\ModifierProvider;

class StoreCode2StoreId extends AbstractModifier implements FieldModifierInterface
{
    private $stores;

    public function __construct($config, \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        parent::__construct($config);
        $stores = $storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->stores[$store->getCode()] = $store->getId();
        }
    }

    public function transform($value)
    {
        if (is_array($value)) {
            foreach ($value as &$storeCode) {
                $storeCode = $this->stores[$storeCode] ?? null;
            }

            return $value;
        }

        return $this->stores[$value] ?? null;
    }

    public function getGroup(): string
    {
        return ModifierProvider::CUSTOM_GROUP;
    }

    public function getLabel(): string
    {
        return __('Store Code to Store Id')->getText();
    }
}
