<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Block\Adminhtml\Actions;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * @var string
     */
    private $formId = 'actions_form_placeholder';

    /**
     * Init layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Ulmod_OrderImportExport::widget/tab/form/placeholder.phtml');

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setFormId($id)
    {
        $this->formId = $id;

        return $this;
    }
}
