<?php

namespace Amasty\ImportCore\Import\Config\Profile;

use Amasty\ImportCore\Api\Config\Profile\FieldFilterExtensionInterfaceFactory;
use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

class FieldFilter implements FieldFilterInterface
{
    /**
     * @var string|null
     */
    private $field;

    /**
     * @var string|null
     */
    private $condition;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var ConfigClassInterface|null
     */
    private $filterClass;

    /**
     * @var \Amasty\ImportCore\Api\Config\Profile\FieldFilterExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @var FieldFilterExtensionInterfaceFactory
     */
    private $extensionFactory;

    public function __construct(
        FieldFilterExtensionInterfaceFactory $extensionFactory
    ) {
        $this->extensionFactory = $extensionFactory;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): FieldFilterInterface
    {
        $this->field = $field;

        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): FieldFilterInterface
    {
        $this->condition = $condition;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): FieldFilterInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getFilterClass(): ?ConfigClassInterface
    {
        return $this->filterClass;
    }

    public function setFilterClass(?ConfigClassInterface $filterClass): FieldFilterInterface
    {
        $this->filterClass = $filterClass;

        return $this;
    }

    public function getExtensionAttributes(): \Amasty\ImportCore\Api\Config\Profile\FieldFilterExtensionInterface
    {
        if (null === $this->extensionAttributes) {
            $this->extensionAttributes = $this->extensionFactory->create();
        }

        return $this->extensionAttributes;
    }

    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\Profile\FieldFilterExtensionInterface $extensionAttributes
    ): FieldFilterInterface {
        $this->extensionAttributes = $extensionAttributes;

        return $this;
    }
}
