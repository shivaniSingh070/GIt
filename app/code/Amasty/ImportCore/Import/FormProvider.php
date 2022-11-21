<?php

namespace Amasty\ImportCore\Import;

class FormProvider
{
    /**
     * @var \Amasty\ImportCore\Api\FormInterface[]
     */
    private $compositeForm;

    public function __construct(array $compositeForm)
    {
        $this->compositeForm = $compositeForm;
    }

    public function get(string $compositeFormType)
    {
        if (!isset($this->compositeForm[$compositeFormType])) {
            throw new \RuntimeException('No meta');
        }

        return $this->compositeForm[$compositeFormType];
    }
}
