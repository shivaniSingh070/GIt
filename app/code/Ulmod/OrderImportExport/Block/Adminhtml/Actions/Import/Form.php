<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Block\Adminhtml\Actions\Import;

use Magento\Backend\Block\Widget\Form\Generic;
use Ulmod\OrderImportExport\Api\Data\ImportConfigInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Form extends Generic
{
    /**
     * @var ImportConfigInterface
     */
    private $config;

    /**
     * @var Yesno
     */
    private $boolean;

    /**
     * @param ImportConfigInterface $config
     * @param Yesno $boolean
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        ImportConfigInterface $config,
        Yesno $boolean,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->config = $config;
        $this->boolean  = $boolean;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $formData */
        $formData = $this->_formFactory->create(
            [
                'data' =>
                    [
                        'id'      => 'import_form',
                        'action'  => $this->getUrl('*/*/import'),
                        'method'  => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
            ]
        );

        $fieldset = $formData->addFieldset(
            'import_fieldset',
            [
                'legend' => __('Behavior')
            ]
        );

        $invoiceField = $fieldset->addField(
            ImportConfigInterface::CREATE_INVOICE,
            'select',
            [
                'name'     => ImportConfigInterface::CREATE_INVOICE,
                'label'    => __('Create Invoice(s)'),
                'title'    => __('Create Invoice(s)'),
                'values'   => $this->boolean->toArray(),
                'required' => true
            ]
        );

        $fieldset->addField(
            ImportConfigInterface::CREATE_SHIPMENT,
            'select',
            [
                'name'     => ImportConfigInterface::CREATE_SHIPMENT,
                'label'    => __('Create Shipment(s)'),
                'title'    => __('Create Shipment(s)'),
                'values'   => $this->boolean->toArray(),
                'required' => true
            ]
        );

        $creditmemoField = $fieldset->addField(
            ImportConfigInterface::CREATE_CREDIT_MEMO,
            'select',
            [
                'name'     => ImportConfigInterface::CREATE_CREDIT_MEMO,
                'label'    => __('Create Credit Memo(s)'),
                'title'    => __('Create Credit Memo(s)'),
                'values'   => $this->boolean->toArray(),
                'required' => true
            ]
        );

        $fieldset->addField(
            ImportConfigInterface::DELIMITER,
            'text',
            [
                'name'     => ImportConfigInterface::DELIMITER,
                'label'    => __('Field Separator'),
                'title'    => __('Field Separator'),
                'note'     => __('Separator value on the CSV import'),
                'style'    => 'width: 80px;',
                'required' => true
            ]
        );

        $fieldset->addField(
            ImportConfigInterface::ENCLOSURE,
            'text',
            [
                'name'     => ImportConfigInterface::ENCLOSURE,
                'label'    => __('Field Enclosure'),
                'title'    => __('Field Enclosure'),
                'note'     => __('Enclosure value on the CSV import'),
                'style'    => 'width: 80px;',
                'required' => true
            ]
        );
        
        $fieldset->addField(
            ImportConfigInterface::ERROR_LIMIT,
            'text',
            [
                'name'     => ImportConfigInterface::ERROR_LIMIT,
                'label'    => __('Allowed Errors Limit'),
                'title'    => __('Allowed Errors Limit'),
                'note'     => __('Specify number of errors to stop the import process'),
                'class'    => 'validate-greater-than-zero validate-number',
                'style'    => 'width: 80px;',
                'required' => true
            ]
        );

        // file upload
        $fieldset = $formData->addFieldset(
            'upload_fieldset',
            [
                'legend' => __('File to Import')
            ]
        );
        $fieldset->addField(
            'file',
            'file',
            [
                'name'     => 'file',
                'label'    => __('Select File to Import'),
                'title'    => __('Select File to Import'),
                'note' => __(
                    'File must be saved in UTF-8 encoding for proper import'
                ),
                'required' => true
            ]
        );

        // actions upload
        $fieldset = $formData->addFieldset(
            'actions_fieldset',
            [
                'legend' => __('Actions')
            ]
        );
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label'          => __('Import Orders'),
                'class'          => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#import_form'
                        ]
                    ]
                ]]
        );

        $fieldset->addField(
            'submit',
            'note',
            [
                'label'       => __(''),
                'title'       => __(''),			
				'text' => $button->toHtml()
			]
        );

        foreach ($fieldset->getElements() as $element) {
            $htmlId = $element->getHtmlId();
            $element->setHtmlId(
                'import_' . $htmlId
            );
        }

        $formData->addFieldNameSuffix('import');
        
        $configData = $this->config->getData();
        $formData->setValues($configData);
        
        $formData->setUseContainer(true);
        
        $this->setForm($formData);

        $dependenceElement = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Form\Element\Dependence::class
        );
        
        $dependenceElement->addFieldMap(
            $invoiceField->getHtmlId(),
            $invoiceField->getName()
        );
        
        $dependenceElement->addFieldMap(
            $creditmemoField->getHtmlId(),
            $creditmemoField->getName()
        );
        
        $dependenceElement->addFieldDependence(
            $creditmemoField->getName(),
            $invoiceField->getName(),
            '1'
        );

        $this->setChild(
            'form_after',
            $dependenceElement
        );

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        $formHtml  = parent::getFormHtml();
        
        $layoutBlock = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Template::class
        );
        
        $layoutBlock->setTemplate(
            'Ulmod_OrderImportExport::actions/form/js.phtml'
        );
        
        $layoutBlock->setFormId(
            'import_form'
        );
        
        $formHtml .= $layoutBlock->toHtml();

        return $formHtml;
    }
}
