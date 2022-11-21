<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Console\Command;

abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * @param array $defaultOptions
     * @param null|string $name
     */
    public function __construct(
        $defaultOptions = [],
        $name = null
    ) {
        $this->defaultOptions = $defaultOptions;
        parent::__construct($name);
    }

    /**
     * Default options
     *
     * @param string $option
     * @return string|null
     */
    public function getDefaultOption($option)
    {
        return array_key_exists($option, $this->defaultOptions) ?
            $this->defaultOptions[$option] :
            null;
    }
}
