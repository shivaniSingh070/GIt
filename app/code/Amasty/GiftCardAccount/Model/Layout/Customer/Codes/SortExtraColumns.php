<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Layout\Customer\Codes;

use Amasty\GiftCardAccount\Model\Layout\Customer\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

class SortExtraColumns implements LayoutProcessorInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    public function process(array $jsLayout): array
    {
        $paths = [
            'components/amcard-account-render/children/extra-column-header/children',
            'components/amcard-account-render/children/extra-column/children'
        ];

        foreach ($paths as $path) {
            $columns = $this->arrayManager->get($path, $jsLayout);
            array_multisort(array_column($columns, 'sortOrder'), SORT_ASC, $columns);
            $this->arrayManager->set($path, $jsLayout, $columns);
        }

        return $jsLayout;
    }
}
