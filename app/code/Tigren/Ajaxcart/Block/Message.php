<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcart\Block;

/**
 * Class Message
 * @package Tigren\Ajaxcart\Block
 */
class Message extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Tigren\Ajaxcart\Helper\Data
     */
    protected $_ajaxcartHelper;

    /**
     * Message constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Tigren\Ajaxcart\Helper\Data $ajaxcartHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Ajaxcart\Helper\Data $ajaxcartHelper,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->_ajaxcartHelper = $ajaxcartHelper;
    }

    /**
     * @return mixed|string
     */
    public function getMessage()
    {
        $message = $this->_ajaxcartHelper->getScopeConfig('ajaxcart/general/message');
        if (!$message) {
            $message = 'You have recently added this product to your Cart';
        }
        return $message;
    }
}