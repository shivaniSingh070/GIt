<?php

namespace Amasty\ExportCore\Api;

interface ActionInterface
{
    public function initialize(ExportProcessInterface $exportProcess);

    public function execute(ExportProcessInterface $exportProcess);
}
