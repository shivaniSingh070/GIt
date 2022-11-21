<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Model\GiftCard\Attributes as GiftCardAttributes;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Amasty\GiftCard\Model\Config\Source\Usage;

/**
 * @codeCoverageIgnore
 */
class UpdateDataTo260
{
    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    public function __construct(
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityType = ProductAttributeInterface::ENTITY_TYPE_CODE;

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::USAGE,
            [
                'type' => 'varchar',
                'label' => 'Usage',
                'backend' => '',
                'frontend' => '',
                'input' => 'select',
                'source' => Usage::class,
                'required' => false,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => AddGiftCardAttributes::GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME,
                'visible_on_front' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true,
                'sort_order' => 1
            ]
        );

        $setup->endSetup();
    }
}
