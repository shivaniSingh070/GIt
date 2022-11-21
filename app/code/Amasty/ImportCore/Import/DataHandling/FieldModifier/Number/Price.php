<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier\Number;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\AbstractModifier;
use Amasty\ImportCore\Import\DataHandling\ModifierProvider;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class Price extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Data $priceHelper,
        StoreManagerInterface $storeManager,
        $config
    ) {
        parent::__construct($config);
        $this->priceHelper = $priceHelper;
        $this->storeManager = $storeManager;
    }

    public function transform($value)
    {
        return strip_tags($this->priceHelper->currencyByStore($value, $this->storeManager->getStore()));
    }

    public function getGroup(): string
    {
        return ModifierProvider::NUMERIC_GROUP;
    }

    public function getLabel(): string
    {
        return __('Price in Base Currency')->getText();
    }
}
