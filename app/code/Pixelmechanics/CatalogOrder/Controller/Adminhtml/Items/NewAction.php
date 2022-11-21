<?php
/**
 * @author: Noshad Ali
 * @package: Pixelmechanics_CatalogOrder
 * @date: 1Aug2019
 * trello: https://trello.com/c/UyuL2qfu/
 */

namespace Pixelmechanics\CatalogOrder\Controller\Adminhtml\Items;

class NewAction extends \Pixelmechanics\CatalogOrder\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
