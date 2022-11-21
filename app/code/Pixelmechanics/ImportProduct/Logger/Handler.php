<?php
/**
* @author : AA
* @template-Version : Magento 2.3.1
* @description : ImportProduct Custom Logger Handler
 * @date : 5.08.2019
 * @Trello: https://trello.com/c/pk8egBYL
*/
namespace Pixelmechanics\ImportProduct\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/productImport.log';
}
