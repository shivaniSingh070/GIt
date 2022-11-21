<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\AbstractModifier;
use Amasty\ImportCore\Import\DataHandling\ModifierProvider;
use Magento\Store\Model\StoreManagerInterface;

class WebsiteCode2WebsiteId extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array|null
     */
    private $map;

    public function __construct($config, StoreManagerInterface $storeManager)
    {
        parent::__construct($config);
        $this->storeManager = $storeManager;
    }

    public function transform($value)
    {
        $map = $this->getMap();
        return $map[$value] ?? $value;
    }

    private function getMap()
    {
        if (!$this->map) {
            $this->map = ['All Websites' => '0'];
            foreach ($this->storeManager->getWebsites() as $website) {
                $this->map[$website->getCode()] = (string)$website->getId();
            }
        }
        return $this->map;
    }

    public function getGroup(): string
    {
        return ModifierProvider::CUSTOM_GROUP;
    }

    public function getLabel(): string
    {
        return __('Website Code to Website Id')->getText();
    }
}
