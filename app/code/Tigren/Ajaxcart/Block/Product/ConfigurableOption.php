<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcart\Block\Product;

/**
 * Class ConfigurableOption
 * @package Tigren\Ajaxcart\Block\Product
 */
class ConfigurableOption extends \Magento\Framework\View\Element\Template
{

    /**
     * @return mixed
     */
    public function getColorLabel()
    {
        return $this->_request->getParam('colorLabel');
    }

    /**
     * @return mixed
     */
    public function getSizeLabel()
    {
        return $this->_request->getParam('sizeLabel');
    }
}