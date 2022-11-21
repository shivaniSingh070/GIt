<?php

namespace Amasty\ImportCore\Api;

interface ActionInterface
{
    public function initialize(ImportProcessInterface $importProcess): void;
    public function execute(ImportProcessInterface $importProcess): void;
}
