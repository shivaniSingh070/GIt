<?php

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\RegistryContainer;

/**
 * Class Identifierexists
 */
class Identifierexists extends Element
{
    protected $type = RegistryContainer::TYPE_CUSTOM_FIELD;

    protected $tag = 'g:identifier_exists';

    protected $format = 'as_is';

    protected $value = 'TRUE';

    protected $template = '<:tag>:value</:tag>' . PHP_EOL;
}
