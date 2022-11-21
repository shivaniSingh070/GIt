<?php

namespace Amasty\Feed\Model\GoogleWizard;

/**
 * Class Gender
 */
class Gender extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:gender';

    protected $modify = 'html_escape';

    protected $name = 'gender';

    protected $description = 'Gender of the item';
}
