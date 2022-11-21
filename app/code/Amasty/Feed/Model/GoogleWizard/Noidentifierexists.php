<?php

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\RegistryContainer;

/**
 * Class Noidentifierexists
 */
class Noidentifierexists extends Element
{
    protected $type = RegistryContainer::TYPE_CUSTOM_FIELD;

    protected $tag = 'g:identifier_exists';

    protected $format = 'as_is';

    protected $value = 'FALSE';

    protected $template = '<:tag>:value</:tag>' . PHP_EOL;
}
