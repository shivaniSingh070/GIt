<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Block\Product;

/**
 * Class BundlePackWrapper
 * @package Amasty\Mostviewed\Block\Product
 */
class BundlePackWrapper extends BundlePack
{
    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getParentHtml();
    }
}
