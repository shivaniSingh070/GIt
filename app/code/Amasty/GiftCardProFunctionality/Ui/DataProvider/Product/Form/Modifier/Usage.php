<?php
declare(strict_types=1);

namespace Amasty\GiftCardProFunctionality\Ui\DataProvider\Product\Form\Modifier;

use Amasty\GiftCard\Model\Config\Source\Usage as SourceUsage;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

class Usage extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    public function modifyMeta(array $meta)
    {
        $path = $this->arrayManager->findPath(
            Attributes::USAGE,
            $meta,
            null,
            'children'
        );

        if ($path) {
            $tooltip = __(
                'Select ‘Multiple’ value for a Gift Card that can be used an indefinite number of times till'
                . ' the balance is over. ‘Single’ value should be used for a Gift Card that needs'
                . ' to be applied only once. In this case, the remaining balance will be reset.'
            );
            $meta = $this->arrayManager->merge(
                $path,
                $meta,
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => true,
                                'default' => SourceUsage::MULTIPLE,
                                'tooltip' => [
                                    'description' => $tooltip
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }

        return $meta;
    }

    public function modifyData(array $data)
    {
        return $data;
    }
}
