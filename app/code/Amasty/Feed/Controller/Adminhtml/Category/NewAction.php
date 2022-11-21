<?php

namespace Amasty\Feed\Controller\Adminhtml\Category;

use Amasty\Feed\Controller\Adminhtml\AbstractCategory;

/**
 * Class NewAction
 *
 * @package Amasty\Feed
 */
class NewAction extends AbstractCategory
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
