<?php

namespace Amasty\GdprCookie\Model\EntityVersion;

interface UpdateSensitiveEntityInterface
{
    /**
     * @return array
     */
    public function getSensitiveData(): array;
}
