<?php

namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Controller\Adminhtml\AbstractFeed;

/**
 * Class NewAction
 *
 * @package Amasty\Feed
 */
class NewAction extends AbstractFeed
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
