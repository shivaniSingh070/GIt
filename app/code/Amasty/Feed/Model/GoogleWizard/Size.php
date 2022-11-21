<?php

namespace Amasty\Feed\Model\GoogleWizard;

/**
 * Class Size
 */
class Size extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:size';

    protected $modify = 'html_escape';

    protected $name = 'size';

    protected $description = 'Size of the item';

    protected $limit = 100;
}
