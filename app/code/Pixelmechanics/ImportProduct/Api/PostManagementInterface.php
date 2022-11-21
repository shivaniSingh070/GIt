<?php

/**
 * @author : AA
 * @template-Version : Magento 2.3.1
 * @description : ImportProduct Interface
 * @date : 5.08.2019
 * @Trello: https://trello.com/c/pk8egBYL
 */

namespace Pixelmechanics\ImportProduct\Api;

interface PostManagementInterface
{
    /**
     * @api
     *
     * @return \Pixelmechanics\ImportProduct\Api\PostManagementInterface
     */
    public function getPost();
}