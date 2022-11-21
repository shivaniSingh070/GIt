<?php
/**
* Extend the Paypal Model NVP class via plugin to disable the shipping address information for Paypal requests
* PM PS, 16.05.20, @link - https://trello.com/c/n4Ew46vE/122-2020-05-2-bestellungen-nicht-in-nav-%C3%BCbergeben-paypal-express
*/
namespace Pixelmechanics\Paypal\Plugin\Model\Api;

/**
 * Class NvpPlugin
 * @package Pixelmechanics\Paypal\Plugin\Model\Api
 */
class NvpPlugin
{
    /**
     * @param \Magento\Paypal\Model\Api\Nvp $subject
     * @param callable $proceed
     * @param $methodName
     * @param array $request
     * @return mixed
     */
    public function aroundCall(\Magento\Paypal\Model\Api\Nvp $subject, callable $proceed, $methodName, array $request)
    {
        $request['NOSHIPPING'] = 1; // Enable no shipping in the request parameter
        return $proceed($methodName, $request);
    }
}