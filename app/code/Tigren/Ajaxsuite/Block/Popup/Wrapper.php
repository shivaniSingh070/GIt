<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxsuite\Block\Popup;

/**
 * Class Wrapper
 * @package Tigren\Ajaxsuite\Block\Popup
 */
class Wrapper extends \Magento\Framework\View\Element\Template
{

    /**
     * Wrapper constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }


}