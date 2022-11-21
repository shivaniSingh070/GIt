<?php

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

/**
 * Class Color
 */
class Color extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:color';

    protected $modify = 'html_escape';

    protected $name = 'color';

    protected $description = 'Color of the item';

    protected $limit = 100;
}
