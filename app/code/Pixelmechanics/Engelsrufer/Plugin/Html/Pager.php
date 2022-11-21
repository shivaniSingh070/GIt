<?php
/**
 * @author NA
 * description: set the limiter to wishlist page in my Account section
 * date: 29.05.2019
 */
namespace Pixelmechanics\Engelsrufer\Plugin\Html;


class Pager
{
	/**
     * The list of available pager limits
     *
     * @var array
     */
	

    public $_availableLimit = [12 => 12, 24 => 24, 48 => 48];

    public function setAvailableLimit(array $limits)
    {
        $this->_availableLimit = $limits;
        return $this;
    }

    public function afterGetAvailableLimit()
    {
      
        return $this->_availableLimit;
    }
}