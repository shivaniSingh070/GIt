<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Customer;

use Amasty\GiftCardAccount\Model\Layout\Customer\Codes\SortButtons;
use Amasty\GiftCardAccount\Model\Layout\Customer\Codes\SortExtraColumns;
use Amasty\GiftCardAccount\Model\Layout\Customer\LayoutProcessorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Cards extends Template
{
    /**
     * @var LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
        $this->layoutProcessors['sortButtons'] = $objectManager->get(SortButtons::class);
        $this->layoutProcessors['sortExtraColumns'] = $objectManager->get(SortExtraColumns::class);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
    }

    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return parent::getJsLayout();
    }
}
