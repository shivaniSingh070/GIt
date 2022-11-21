<?php

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product as ExportProduct;

/**
 * Class Brand
 */
class Brand extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:brand';

    protected $modify = 'html_escape';

    protected $value = ExportProduct::PREFIX_PRODUCT_ATTRIBUTE . '|manufacturer';

    protected $name = 'brand';

    protected $description = 'Brand of the item';

    protected $limit = 70;
}
