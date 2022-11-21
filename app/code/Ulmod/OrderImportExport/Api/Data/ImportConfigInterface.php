<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Api\Data;

interface ImportConfigInterface
{
    /**
     * Constants for keys of data.
     */
    const IMPORT_ORDER_NUMBER = 'import_order_number';
    const DELIMITER           = 'delimiter';
    const ENCLOSURE           = 'enclosure';
    const ERROR_LIMIT         = 'error_limit';
    const CREATE_INVOICE      = 'create_invoice';
    const CREATE_SHIPMENT     = 'create_shipment';
    const CREATE_CREDIT_MEMO  = 'create_credit_memo';

    /**
     * @param string $enclosure
     *
     * @return $this
     */
    public function setEnclosure($enclosure);

    /**
     * @return string
     */
    public function getEnclosure();

    /**
     * @param string $delimiter
     *
     * @return $this
     */
    public function setDelimiter($delimiter);

    /**
     * @return string
     */
    public function getDelimiter();

    /**
     * @param int|bool $bool
     *
     * @return $this
     */
    public function setImportOrderNumber($bool);

    /**
     * @return int
     */
    public function getImportOrderNumber();

    /**
     * @param int $int
     *
     * @return $this
     */
    public function setErrorLimit($int);

    /**
     * @return int
     */
    public function getErrorLimit();
    
    /**
     * @param int|bool $bool
     *
     * @return $this
     */
    public function setCreateShipment($bool);

    /**
     * @return int
     */
    public function getCreateShipment();

    /**
     * @param int|bool $bool
     *
     * @return $this
     */
    public function setCreateInvoice($bool);

    /**
     * @return int
     */
    public function getCreateInvoice();

    /**
     * @param int|bool $bool
     *
     * @return $this
     */
    public function setCreateCreditMemo($bool);

    /**
     * @return int
     */
    public function getCreateCreditMemo();
}
