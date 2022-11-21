<?php

namespace Amasty\ExportCore\Export\Temp;

class HelperInitializePlugin
{
    /**
     * @var HelperFactory
     */
    private $helperFactory;

    public function __construct(HelperFactory $helperFactory)
    {
        $this->helperFactory = $helperFactory;
    }

    public function afterInitialize(\Amasty\ExportCore\Api\ExportProcessInterface $subject)
    {
        $subject->getExtensionAttributes()->setHelper($this->helperFactory->create());

        return $subject;
    }
}
