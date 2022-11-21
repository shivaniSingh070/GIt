<?php

namespace Amasty\Feed\Controller\Adminhtml;

/**
 * Class AbstractField
 *
 * @package Amasty\Feed
 */
abstract class AbstractField extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin action
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Feed::feed_field';
}
