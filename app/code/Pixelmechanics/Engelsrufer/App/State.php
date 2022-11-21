<?php 
/**
 * Override \Magento\Framework\App\State 
 * for Amasty module "Area code is not set" || "Area code is set" issue
 * PM AJ 14.12.21
 * 
 */
namespace Pixelmechanics\Engelsrufer\App;

class State extends \Magento\Framework\App\State
{   
    /**
     * check if _areaCode is set or not
     * @return int
     */
    public function validateAreaCode()
    {
        if (!isset($this->_areaCode)) {
            return false;
        }
        return true;
    }
}