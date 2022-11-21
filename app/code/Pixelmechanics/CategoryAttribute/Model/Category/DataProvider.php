<?php

/* To Upload Product List Image
 * @package  Pixelmechanics_CategoryAttribute
 * @module   CategoryAttribute
 * Created by AA 22.05.2019
 */

namespace Pixelmechanics\CategoryAttribute\Model\Category;
  
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
  /*
   * Reurn Field productlist_image
   */
    
    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'productlist_image'; // custom image field
         
        return $fields;
    }
}