<?php
/**
 * created 16.05 by HA
 * Helper class to get the value from configuration 
 * trello: https://trello.com/c/E2mdqBZL/244-newsletter-formular-%C3%A4nderung
 * 
 */
namespace Hm\Newsletters\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

	const XML_PATH_MAILCHIMPCONFIGURATION = 'MailChimpConfiguration/';

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function getGeneralConfig($code, $storeId = null)
	{

		return $this->getConfigValue(self::XML_PATH_MAILCHIMPCONFIGURATION .'general/'. $code, $storeId);
	}

}