<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class General
 * @package Amasty\Mostviewed\Block\Adminhtml\System\Config
 * @codingStandardsIngoreFile
 */
class General extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Return header comment part of html for fieldset
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        return sprintf(
            '<div class="comment">%s<a target="_blank" href="%s">%s</a></div>',
            __('To configure the rules please go to Catalog -> Amasty Related Products -> '),
            $this->getUrl('amasty_mostviewed/product_group/'),
            __('Related Product Rules')
        );
    }
}
