<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Rule\Condition;

/**
 * Class SameAsCombine
 * @package Amasty\Mostviewed\Model\Rule\Condition
 * @codingStandardsIgnoreFile
 */
class SameAsCombine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Mostviewed\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {
        parent::__construct($context, $conditionFactory, $data);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Amasty\Mostviewed\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Custom Fields'),
                    'value' => [
                        [
                            'label' => __('Price'),
                            'value' => 'Amasty\Mostviewed\Model\Rule\Condition\Price',
                        ],
                    ]
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return 'same_as_conditions';
    }

    /**
     * @return mixed
     */
    public function getSameAsConditions()
    {
        return $this->getData('conditions');
    }
}
