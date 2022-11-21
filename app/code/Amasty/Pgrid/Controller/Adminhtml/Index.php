<?php
namespace Amasty\Pgrid\Controller\Adminhtml;

abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }
}
