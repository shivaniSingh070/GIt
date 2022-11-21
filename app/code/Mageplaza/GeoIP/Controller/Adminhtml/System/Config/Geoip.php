<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GeoIP
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GeoIP\Controller\Adminhtml\System\Config;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Mageplaza\GeoIP\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;
use PharData;
use function stream_get_wrappers;
use function stream_wrapper_restore;

/**
 * Class Geoip
 * @package Mageplaza\GeoIP\Controller\Adminhtml\System\Config
 */
class Geoip extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Geoip constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param DirectoryList $directoryList
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        DirectoryList $directoryList,
        HelperData $helperData,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_directoryList    = $directoryList;
        $this->_helperData       = $helperData;
        $this->_logger           = $logger;

        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $status = false;
        try {
            $path = $this->_directoryList->getPath('var') . '/Mageplaza/GeoIp/GeoIp';
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $folder   = scandir($path, true);
            $pathFile = $path . '/' . $folder[0] . '/GeoLite2-City.mmdb';
            if (file_exists($pathFile)) {
                foreach (scandir($path . '/' . $folder[0], true) as $filename) {
                    if ($filename == '..' || $filename == '.') {
                        continue;
                    }
                    unlink($path . '/' . $folder[0] . '/' . $filename);
                }
                rmdir($path . '/' . $folder[0]);
            }

            file_put_contents($path . '/GeoLite2-City.tar.gz', fopen($this->_helperData->getDownloadPath(), 'r'));
            if (!in_array('phar', stream_get_wrappers(), true)) {
                stream_wrapper_restore('phar');
            }

            $phar = new PharData($path . '/GeoLite2-City.tar.gz');
            $phar->extractTo($path);
            $status  = true;
            $message = __('Download library success!');
        } catch (Exception $e) {
            $this->_logger->info($e->getMessage());
            $message = __('Can\'t download file. Please try again! <br> You can find out more in the error log.' );
        }

        /** @var Json $result */
        $result = $this->resultJsonFactory->create();

        return $result->setData(['success' => $status, 'message' => $message]);
    }
}
