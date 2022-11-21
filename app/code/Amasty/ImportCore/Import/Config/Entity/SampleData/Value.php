<?php

namespace Amasty\ImportCore\Import\Config\Entity\SampleData;

use Amasty\ImportCore\Api\Config\Entity\SampleData\ValueInterface;

class Value implements ValueInterface
{
    private $field;

    private $value;

    /**
     * @inheritDoc
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @inheritDoc
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
