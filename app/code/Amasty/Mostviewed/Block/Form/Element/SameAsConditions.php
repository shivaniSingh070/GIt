<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Block\Form\Element;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class SameAsConditions
 * @package Amasty\Mostviewed\Block\Form\Element
 */
class SameAsConditions implements RendererInterface
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($element->getRule() && $element->getRule()->getSameAsConditions()) {
            return $element->getRule()->getSameAsConditions()->asHtmlRecursive();
        }

        return '';
    }
}
