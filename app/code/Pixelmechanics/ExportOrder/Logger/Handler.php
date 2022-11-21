<?php
/**
* @author : AA
* @template-Version : Magento 2.3.1
* @description : ExportOrder Custom Logger Handler
* @date : 19.06.2019
* @Trello: https://trello.com/c/7yfEDXmg
*/
namespace Pixelmechanics\ExportOrder\Logger;

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
    protected $fileName = '/var/log/orderexport.log';
}
