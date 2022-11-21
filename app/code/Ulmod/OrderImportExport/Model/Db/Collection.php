<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Db;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;

class Collection
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
    }

    /**
     * @param string $date
     * @return string
     */
    public function getToDateFilter($date)
    {
        /** @var \DateTime $datetime */
        $datetime = new \DateTime($date);
        $datetime->setTime(23, 59, 59);
        return $datetime->format(
            StdlibDateTime::DATETIME_PHP_FORMAT
        );
    }

    /**
     * @param string $date
     * @return string
     */
    public function getFromDateFilter($date)
    {
        /** @var \DateTime $datetime */
        $datetime = new \DateTime($date);
        $datetime->setTime(0, 0, 0);
        return $datetime->format(
            StdlibDateTime::DATETIME_PHP_FORMAT
        );
    }
}
