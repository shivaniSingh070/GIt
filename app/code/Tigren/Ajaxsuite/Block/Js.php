<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxsuite\Block;

/**
 * Class Js
 * @package Tigren\Ajaxsuite\Block
 */
class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'js/main.phtml';
    /**
     * @var \Tigren\Ajaxsuite\Helper\Data
     */
    protected $_ajaxsuiteHelper;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * Js constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->_ajaxsuiteHelper = $ajaxsuiteHelper;
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return string
     */
    public function getAjaxLoginUrl()
    {
        return $this->getUrl('ajaxsuite/login');
    }

    /**
     * @return string
     */
    public function getAjaxWishlistUrl()
    {
        return $this->getUrl('ajaxsuite/wishlist');
    }

    /**
     * @return string
     */
    public function getAjaxCompareUrl()
    {
        return $this->getUrl('ajaxsuite/compare');
    }

    /**
     * @return string
     */
    public function getAjaxSuiteInitOptions()
    {
        return $this->_ajaxsuiteHelper->getAjaxSuiteInitOptions();
    }
}