<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class Elastic
 * @package Amasty\Mostviewed\Block\Adminhtml\System\Config\Form\Field
 */
class Elastic extends Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->getModuleManager() && $this->getModuleManager()->isEnabled('Amasty_ElasticSearch')) {
            $element->setValue(__("Installed"));
            $element->setHtmlId('amasty_is_instaled');
            $element->setComment('');
        } else {
            $element->setValue(__('Not Installed'));
            $element->setHtmlId('amasty_not_instaled');
        }

        return parent::render($element);
    }
}
