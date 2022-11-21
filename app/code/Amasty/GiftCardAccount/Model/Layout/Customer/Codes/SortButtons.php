<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Layout\Customer\Codes;

use Amasty\GiftCardAccount\Model\Layout\Customer\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

class SortButtons implements LayoutProcessorInterface
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
        $accountBtns = $this->arrayManager->get(
            'components/amcard-account-render/children/action-button/children',
            $jsLayout
        );
        array_multisort(array_column($accountBtns, 'sortOrder'), SORT_ASC, $accountBtns);
        $this->arrayManager->set(
            'components/amcard-account-render/children/action-button/children',
            $jsLayout,
            $accountBtns
        );

        return $jsLayout;
    }
}
