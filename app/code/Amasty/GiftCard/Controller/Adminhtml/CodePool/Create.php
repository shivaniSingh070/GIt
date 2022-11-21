<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;

class Create extends AbstractCodePool
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
