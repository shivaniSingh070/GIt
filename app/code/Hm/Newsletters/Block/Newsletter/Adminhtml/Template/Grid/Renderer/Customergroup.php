<?php
/**
 * this class to use grid renderer
 * created by HA 16.05.2022 
 * related to card https://trello.com/c/E2mdqBZL/244-newsletter-formular-%C3%A4nderung 
 */
namespace Hm\Newsletters\Block\Newsletter\Adminhtml\Template\Grid\Renderer;
use Magento\Framework\DataObject;
class Customergroup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        if($row->getData('type')==1){
            return ($row->getData('c_group')!=''?$row->getData('c_group'):'----');
        }else{
            return ($row->getData('c_group')!=''?$row->getData('c_group'):'----');
        }
    }
}