<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\Image;

use Amasty\GiftCard\Controller\Adminhtml\AbstractImage;

class Create extends AbstractImage
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
