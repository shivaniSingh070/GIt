<?php
/**
 * this class to use grid renderer for Cosutmer date of brith
 * created by HA 14.06.2022 
 * related to card https://trello.com/c/E2mdqBZL/244-newsletter-formular-%C3%A4nderung 
 */
namespace Hm\Newsletters\Block\Newsletter\Adminhtml\Template\Grid\Renderer;
use Magento\Framework\DataObject;
class Customerdateofbirth extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        
            return ($row->getData('c_dateofbirth')!=''?$row->getData('c_dateofbirth'):'----');
      
    }
}