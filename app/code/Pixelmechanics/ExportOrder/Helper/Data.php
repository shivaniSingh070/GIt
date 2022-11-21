<?php

/**
 * @author : AA
 * @template-Version : Magento 2.3.1
 * @description : ExportOrder helper
 * @date : 19.06.2019
 * @Trello: https://trello.com/c/7yfEDXmg
 */

namespace Pixelmechanics\ExportOrder\Helper;

class Data extends \Magento\Framework\Url\Helper\Data {

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;

    /**
     * @var \Pixelmechanics\ExportOrder\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_rule;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
       \Magento\Framework\Filesystem\DirectoryList $dir,
       \Magento\SalesRule\Model\Rule $rule, \Pixelmechanics\ExportOrder\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->_dir = $dir;
        $this->_rule = $rule;
        $this->_logger = $logger;
    }

    /**
     * @description : Get the payment informations
     * @return : Array
     */
    public function getPaymentMethods() {
        $payment_methods = array(
            "checkmo" => "Rechnung",
            "debit" => "Bankeinzug/Lastschrift",
            "bankpayment" => "Vorkasse",
            "paypal_express" => "Paypal",
        );

        return $payment_methods;
    }

    /*
     * @Description : get the directory paths.
     * @return String
     */

    public function getDirectoryPath($path = null) {
        if ($path != null) {
            return $this->_dir->getPath($path);
        } else {
            return $this->_dir->getRoot();
        }
    }

    /**
     * @description : Get the country code informations
     * @return : Array
     */
    public function getOrderExportDirectoryPath() {
        return $this->getDirectoryPath('var').'/export/orders/';
    }

    /**
     * @description : Get the country code informations
     * @return : Array
     */
    public function getFilenameOfOrderExport($exportDirectory, $currentDate, $order_id) {
        return $exportDirectory . 'orderexport' . "_" . $currentDate  . "_" . $order_id . '.xml';
    }

    /**
     * @description : generate the log
     * @return : Log
     */
    public function logOrderExport($message, $type = "info") {
        // Get current month for log file naming
        $current_month = date('m_Y');

        // Get log level (defined in lib\Zend\log.php).
        $levels = array(
            'error' => 3,
            'warning' => 4,
            'info' => 6,
        );

        $level = $levels[$type];
        $this->_logger->info($message);
    }

    /**
     * Function to format the price: Rounded to 2 decimals, without thousand separator.
     *
     * @param int $price
     * */
    public function formatPrice($price) {
        return number_format(round($price, 2), 2, ".", "");
    }



}
