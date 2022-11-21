<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOrderNumber
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOrderNumber\Model\ResourceModel;

/**
 * Class Sequence represents sequence in logic
 */
class Sequence extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
     /**
     * @const ALPHA_NUMERIC
     */
    const ALPHA_NUMERIC = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * AppResource
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Context AppResource
     */
    protected $connection;

    /**
     * Helper
     *
     * @var \Bss\CustomOrderNumber\Helper\Data
     */
    protected $helper;

    /**
     * DateTime
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    /**
     * Meta
     *
     * @var \Magento\SalesSequence\Model\ResourceModel\Meta
     */
    protected $meta;


    /**
     * Construct
     *
     * @param \Bss\CustomOrderNumber\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\SalesSequence\Model\ResourceModel\Meta $meta
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Bss\CustomOrderNumber\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\SalesSequence\Model\ResourceModel\Meta $meta,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        $this->helper = $helper;
        $this->datetime = $datetime;
        $this->meta = $meta;
        $this->connection = $context->getResources();
        parent::__construct($context, $connectionName);
    }


    /**
     * Abstract Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_sequence_meta', 'meta_id');
    }

    /**
     * Retrieve Sequence Table
     *
     * @param string $entityType
     * @param int $storeId
     * @return string
     */
    public function getSequenceTable($entityType, $storeId)
    {
        $meta = $this->meta->loadByEntityTypeAndStore($entityType, $storeId);
        $sequenceTable = $meta->getSequenceTable();
        return $sequenceTable;
    }

    /**
     * Retrieve counter
     *
     * @param string $table
     * @param int $startValue
     * @param int $step
     * @param string $pattern
     * @return int
     */
    public function counter($table, $startValue, $step, $pattern)
    {
        $this->connection->getConnection('sales')->insert($table, []);
        $lastIncrementId = $this->connection->getConnection('sales')->lastInsertId($table);
        if ($lastIncrementId > 2) {
            $currentId = ($lastIncrementId*$step)/2 + ($startValue - $step);
        } else {
            $currentId = $startValue;
        }
        $counter = sprintf($pattern, $currentId);
        return $counter;
    }

    /**
     * Retrieve counter
     *
     * @param string $table
     * @param int $startValue
     * @param int $step
     * @param string $pattern
     * @return int
     */
    public function counterPlusOne($table, $startValue, $step, $pattern)
    {
        $this->connection->getConnection('sales')->insert($table, []);
        $lastIncrementId = $this->connection->getConnection('sales')->lastInsertId($table);
        if ($lastIncrementId > 1) {
            $currentId = ($lastIncrementId*$step) + ($startValue - $step);
        } else {
            $currentId = $startValue;
        }
        $counter = sprintf($pattern, $currentId);
        return $counter;
    }

    /**
     * Retrieve currentId
     *
     * @param string $format
     * @param int $storeId
     * @param string $counter
     * @return string
     */
    public function replace($format, $storeId, $counter)
    {
        $timezone = $this->helper->timezone($storeId);
        $gmtOffset = $this->datetime->calculateOffset($timezone);
        $timestamp = $this->datetime->timestamp() + $gmtOffset;
        $date = $this->datetime->date(null, $timestamp);

        $dd = date('d', strtotime($date));
        $d = (int)$dd;
        $mm = date('m', strtotime($date));
        $m = (int)$mm;
        $yy = date('y', strtotime($date));
        $yyyy = date('Y', strtotime($date));

        $rndNumbers = '';
        $rndLetters = '';
        $rndAlphanumeric = '';

        if (strpos($format, '{rndNumbers') !== false) {
            $rnd = "rndNumbers";
            $index = strpos($format, $rnd) + strlen($rnd);
            $length = substr($format, $index, 2);
            $revert = $rnd.$length;
            $format = str_replace($revert, $rnd, $format);
            $rndNumbers = $this->rndNumbers((int)$length);
        }

        if (strpos($format, '{rndnumbers') !== false) {
            $rnd = "rndnumbers";
            $index = strpos($format, $rnd) + strlen($rnd);
            $length = substr($format, $index, 2);
            $revert = $rnd.$length;
            $format = str_replace($revert, $rnd, $format);
            $rndNumbers = $this->rndNumbers((int)$length);
        }

        if (strpos($format, '{rndLetters') !== false) {
            $rnd = "rndLetters";
            $index = strpos($format, $rnd) + strlen($rnd);
            $length = substr($format, $index, 2);
            $revert = $rnd.$length;
            $format = str_replace($revert, $rnd, $format);
            $rndLetters = $this->rndLetters((int)$length);
        }

        if (strpos($format, '{rndletters') !== false) {
            $rnd = "rndletters";
            $index = strpos($format, $rnd) + strlen($rnd);
            $length = substr($format, $index, 2);
            $revert = $rnd.$length;
            $format = str_replace($revert, $rnd, $format);
            $rndLetters = $this->rndLetters((int)$length);
        }

        if (strpos($format, '{rndAlphanumeric') !== false) {
            $rnd = "rndAlphanumeric";
            $index = strpos($format, $rnd) + strlen($rnd);
            $length = substr($format, $index, 2);
            $revert = $rnd.$length;
            $format = str_replace($revert, $rnd, $format);
            $rndAlphanumeric = $this->rndAlphanumeric((int)$length);
        }

        if (strpos($format, '{rndalphanumeric') !== false) {
            $rnd = "rndalphanumeric";
            $index = strpos($format, $rnd) + strlen($rnd);
            $length = substr($format, $index, 2);
            $revert = $rnd.$length;
            $format = str_replace($revert, $rnd, $format);
            $rndAlphanumeric = $this->rndAlphanumeric((int)$length);
        }
        
        $search     = ['{d}','{dd}','{m}','{mm}','{yy}','{yyyy}','{storeId}','{storeid}','{storeID}','{counter}',
            '{rndNumbers}', '{rndnumbers}', '{rndLetters}', '{rndletters}', '{rndAlphanumeric}', '{rndalphanumeric}'];
        $replace    = [$d, $dd, $m, $mm, $yy, $yyyy, $storeId, $storeId, $storeId, $counter, $rndNumbers, 
            $rndNumbers, $rndLetters, $rndLetters, $rndAlphanumeric, $rndAlphanumeric];

        $result = str_replace($search, $replace, $format);

        return $result;
    }

    /**
     * Check Unique
     *
     * @param string $result
     * @return int
     */
    public function checkUnique($result, $storeId)
    {
        $table = $this->getTable('sales_order');
        $select = $this->connection->getConnection('sales')->select()
                    ->from(
                        ['ce' => $table],
                        ['increment_id']
                    )
                    ->where('increment_id = ? ',$result)
                    ->where('store_id = ? ',$storeId);
        $data = $this->connection->getConnection('sales')->fetchOne($select);
        if (empty($data)) {
            return '1';
        } else {
            return '0';
        }
    }

    /**
     * @param $result
     * @param $storeId
     * @return string
     */
    public function checkUniqueInvoice($result, $storeId)
    {
        $table = $this->getTable('sales_invoice_grid');
        $select = $this->connection->getConnection('invoice')->select()
            ->from(
                ['ce' => $table],
                ['increment_id']
            )
            ->where('increment_id = ? ',$result)
            ->where('store_id = ? ',$storeId);
        $data = $this->connection->getConnection('invoice')->fetchOne($select);
        if (empty($data)) {
            return '1';
        } else {
            return '0';
        }
    }

    /**
     * @param $result
     * @param $storeId
     * @return string
     */
    public function checkUniqueShipment($result, $storeId)
    {
        $table = $this->getTable('sales_shipment_grid');
        $select = $this->connection->getConnection('shipment')->select()
            ->from(
                ['ce' => $table],
                ['increment_id']
            )
            ->where('increment_id = ? ',$result)
            ->where('store_id = ? ',$storeId);
        $data = $this->connection->getConnection('shipment')->fetchOne($select);
        if (empty($data)) {
            return '1';
        } else {
            return '0';
        }
    }

    /**
     * @param $result
     * @param $storeId
     * @return string
     */
    public function checkUniqueCreditMemo($result, $storeId)
    {
        $table = $this->getTable('sales_creditmemo_grid');
        $select = $this->connection->getConnection('credit')->select()
            ->from(
                ['ce' => $table],
                ['increment_id']
            )
            ->where('increment_id = ? ',$result)
            ->where('store_id = ? ',$storeId);
        $data = $this->connection->getConnection('credit')->fetchOne($select);
        if (empty($data)) {
            return '1';
        } else {
            return '0';
        }
    }
    
    /**
     * Get Extra
     *
     * @param string $entityType
     * @param int $storeId
     * @return int
     */
    public function extra($entityType, $storeId)
    {
        $table = $this->getSequenceTable($entityType, $storeId);
        $this->connection->getConnection('sales')->insert($table, []);
        $extra = '-'.$this->connection->getConnection('sales')->lastInsertId($table);
        return $extra;
    }

    /**
     * Set CronOrder
     *
     * @param int $storeId
     * @param int $frequency
     * @return void
     */
    public function setCronOrder($storeId, $frequency)
    {
        $entityType = 'order';
        if ($this->helper->isOrderEnable($storeId)) {
            if ($this->helper->getOrderReset($storeId) == $frequency) {
                $table = $this->getSequenceTable($entityType, $storeId);
                $this->connection->getConnection('sales')->truncateTable($table);
            }        
        }
    }

    /**
     * Set CronInvoice
     *
     * @param int $storeId
     * @param int $frequency
     * @return void
     */
    public function setCronInvoice($storeId, $frequency)
    {
        $entityType = 'invoice';
        if ($this->helper->isInvoiceEnable($storeId) && (!$this->helper->isInvoiceSameOrder($storeId))) {
            if ($this->helper->getInvoiceReset($storeId) == $frequency) {
                $table = $this->getSequenceTable($entityType, $storeId);
                $this->connection->getConnection('sales')->truncateTable($table);
            }      
        }
    }

    /**
     * Set CronShipment
     *
     * @param int $storeId
     * @param int $frequency
     * @return void
     */
    public function setCronShipment($storeId, $frequency)
    {
        $entityType = 'shipment';
        if ($this->helper->isShipmentEnable($storeId) && (!$this->helper->isShipmentSameOrder($storeId))) {
            if ($this->helper->getShipmentReset($storeId) == $frequency) {
                $table = $this->getSequenceTable($entityType, $storeId);
                $this->connection->getConnection('sales')->truncateTable($table);
            }      
        }
    }

    /**
     * Set CronCreditmemo
     *
     * @param int $storeId
     * @param int $frequency
     * @return void
     */
    public function setCronCreditmemo($storeId, $frequency)
    {
        $entityType = 'creditmemo';
        if ($this->helper->isShipmentEnable($storeId) && (!$this->helper->isShipmentSameOrder($storeId))) {
            if ($this->helper->getShipmentReset($storeId) == $frequency) {
                $table = $this->getSequenceTable($entityType, $storeId);
                $this->connection->getConnection('sales')->truncateTable($table);
            }
        }
    }

    /**
     * Set Cron
     *
     * @param int $storeId
     * @param int $frequency
     * @return void
     */
    public function setCron($storeId, $frequency)
    {
        $this->setCronOrder($storeId, $frequency);
        $this->setCronInvoice($storeId, $frequency);
        $this->setCronShipment($storeId, $frequency);
        $this->setCronCreditmemo($storeId, $frequency);
    }

    /**
     * Reset Sequence
     *
     * @param string $entityType
     * @param int $storeId
     * @return void
     */
    public function resetSequence($entityType, $storeId)
    {
        $table = $this->getSequenceTable($entityType, $storeId);
        $this->connection->getConnection('sales')->truncateTable($table);
    }

    /**
     * Retrieve numbers
     *
     * @param int $length
     * @return int
     */
    public function rndNumbers($length)
    {
        $numbers ='';
        $i=0;
        while ($i<$length) {
            $position = rand(0, 9);
            $numbers=$numbers.substr(self::ALPHA_NUMERIC, $position, 1);
            $i++;
        }
        return $numbers;
    }

    /**
     * Retrieve letters
     *
     * @param int $length
     * @return string
     */
    public function rndLetters($length)
    {
        $letters ='';
        $i=0;
        while ($i<$length) {
            $position = rand(10, 35);
            $letters=$letters.substr(self::ALPHA_NUMERIC, $position, 1);
            $i++;
        }
        return $letters;
    }

    /**
     * Retrieve alphanumeric
     *
     * @param int $length
     * @return string
     */
    public function rndAlphanumeric($length)
    {
        $alphanumeric ='';
        $i=0;
        while ($i<$length) {
            $position = rand(0, 35);
            $alphanumeric=$alphanumeric.substr(self::ALPHA_NUMERIC, $position, 1);
            $i++;
        }
        return $alphanumeric;
    }
}
