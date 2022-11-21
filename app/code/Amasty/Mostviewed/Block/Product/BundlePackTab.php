<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Block\Product;

/**
 * Class BundlePackTab
 * @package Amasty\Mostviewed\Block\Product
 */
class BundlePackTab extends BundlePack
{
    /**
     * @return string
     */
    public function toHtml()
    {
        $html = trim(parent::toHtml());
        if ($html) {
            $this->setTitle($this->config->getTabTitle());
        }

        return $html;
    }
}
