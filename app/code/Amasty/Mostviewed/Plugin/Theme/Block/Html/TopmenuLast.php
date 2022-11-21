<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Plugin\Theme\Block\Html;

use Amasty\Mostviewed\Model\OptionSource\TopMenuLink;

/**
 * Class TopmenuLast
 * @package Amasty\Mostviewed\Plugin\Theme\Block\Html
 */
class TopmenuLast extends Topmenu
{
    /**
     * @return int
     */
    protected function getPosition()
    {
        return TopMenuLink::DISPLAY_LAST;
    }
}
