<?php

namespace Amasty\ImportCore\Api\Config\Entity\SampleData;

interface RowInterface
{
    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\SampleData\ValueInterface[]
     */
    public function getValues();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\SampleData\ValueInterface[] $values
     *
     * @return void
     */
    public function setValues($values);
}
