<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Ui\Component\Form;

use Magento\Framework\View\Element\ComponentVisibilityInterface;

class CustomerHistoryFieldset extends \Magento\Ui\Component\Form\Fieldset implements ComponentVisibilityInterface
{
    public function isComponentVisible(): bool
    {
        return $this->context->getRequestParam('id') !== null;
    }
}
