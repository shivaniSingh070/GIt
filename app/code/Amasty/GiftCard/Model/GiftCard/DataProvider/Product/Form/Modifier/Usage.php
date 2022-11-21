<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\DataProvider\Product\Form\Modifier;

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
            $meta = $this->arrayManager->merge(
                $path,
                $meta,
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => false
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
