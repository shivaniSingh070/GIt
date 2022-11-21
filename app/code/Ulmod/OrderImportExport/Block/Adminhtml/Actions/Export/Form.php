<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Block\Adminhtml\Actions\Export;

use Magento\Backend\Block\Widget\Form\Generic;
use Ulmod\OrderImportExport\Api\Data\ExportConfigInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Form extends Generic
{
    /**
     * @var Yesno
     */
    private $boolean;

    /**
     * @var ExportConfigInterface
     */
    private $config;
    
    /**
     * @param Yesno $boolean
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        ExportConfigInterface $config,
        Yesno $boolean,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->boolean = $boolean;
        $this->config  = $config;
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
                        'id'      => 'export_form',
                        'action'  => $this->getUrl('*/*/export'),
                        'method'  => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
            ]
        );
        
        // behavior
        $fieldset = $formData->addFieldset(
            'export_fieldset',
            [
                'legend' => __('Behavior')
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::GREGORIAN
        );

        $fieldset->addField(
            ExportConfigInterface::FROM,
            'date',
            [
                'name'        => ExportConfigInterface::FROM,
                'label'       => __('From'),
                'title'       => __('From'),
                'date_format' => $dateFormat,
                'required'    => false
            ]
        );

        $fieldset->addField(
            ExportConfigInterface::TO,
            'date',
            [
                'name'        => ExportConfigInterface::TO,
                'label'       => __('To'),
                'title'       => __('To'),
                'date_format' => $dateFormat,
                'required'    => false
            ]
        );

        $fieldset->addField(
            ExportConfigInterface::DELIMITER,
            'text',
            [
                'name'     => ExportConfigInterface::DELIMITER,
                'label'    => __('Field Separator'),
                'title'    => __('Field Separator'),
                'note'     => __('Separator value on the CSV export'),
                'style'    => 'width: 80px;',
                'required' => true
            ]
        );

        $fieldset->addField(
            ExportConfigInterface::ENCLOSURE,
            'text',
            [
                'name'     => ExportConfigInterface::ENCLOSURE,
                'label'    => __('Field Enclosure'),
                'title'    => __('Field Enclosure'),
                'note'     => __('Enclosure value on the CSV export'),
                'style'    => 'width: 80px;',
                'required' => true
            ]
        );

        $fieldset->addField(
            ExportConfigInterface::FILENAME,
            'hidden',
            ['name' => ExportConfigInterface::FILENAME]
        );

        $fieldset->addField(
            ExportConfigInterface::DIRECTORY,
            'hidden',
            ['name' => ExportConfigInterface::DIRECTORY]
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
                'label'          => __('Export Orders'),
                'class'          => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#export_form'
                        ]
                    ]
                ]]
        );

        $fieldset->addField(
            'submit',
            'note',
            [
				'text' => $button->toHtml(),
                'label'       => __(''),
                'title'       => __('')				
			]
        );

        foreach ($fieldset->getElements() as $element) {
            $htmlId = $element->getHtmlId();
            $element->setHtmlId(
                'export_' . $htmlId
            );
        }

        $formData->addFieldNameSuffix('export');
        
        $configData = $this->config->getData();
        $formData->setValues($configData);
        
        $formData->setUseContainer(true);
        
        $this->setForm($formData);

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
            'export_form'
        );
        
        $formHtml .= $layoutBlock->toHtml();

        return $formHtml;
    }
}
