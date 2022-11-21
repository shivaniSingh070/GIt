<?php

/**
 * @template-Version : Magento 2.3.1
 * @description : ExportOrder Interface
 * @author : PM RH 
 * @date : 11.12.2019
 * @link: https://trello.com/c/68IXDl4E/61-order-export-bei-paypal-manchmal-ohne-items
 */

namespace Pixelmechanics\ExportOrder\Api;

interface PostManagementInterface
{
    /**
     * @api
     *
     * @return \Pixelmechanics\ExportOrder\Api\PostManagementInterface
     */
    public function getPost();
}