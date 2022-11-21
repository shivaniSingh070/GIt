<?php

namespace Amasty\Feed\Controller\Adminhtml\Field;

use Amasty\Feed\Controller\Adminhtml\AbstractField;

/**
 * Class NewAction
 *
 * @package Amasty\Feed
 */
class NewAction extends AbstractField
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
