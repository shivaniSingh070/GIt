<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcart\Block;


/**
 * Class Js
 * @package Tigren\Ajaxcart\Block
 */
class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'js/main.phtml';

    /**
     * @var \Tigren\Ajaxcart\Helper\Data
     */
    protected $_ajaxcartHelper;

    /**
     * Js constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Tigren\Ajaxcart\Helper\Data $ajaxcartHelper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Ajaxcart\Helper\Data $ajaxcartHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ajaxcartHelper = $ajaxcartHelper;
        $this->formKey = $formKey;
    }

    /**
     * @return string
     */
    public function getAjaxCartInitOptions()
    {
        return $this->_ajaxcartHelper->getAjaxCartInitOptions();
    }

    /**
     * @return string
     */
    public function getAjaxSidebarInitOptions()
    {
        $icon = $this->getViewFileUrl('images/loader-1.gif');
        return $this->_ajaxcartHelper->getAjaxSidebarInitOptions($icon);
    }

}