<?php
/* Creted Helper for module
 * @package  Pixelmechanics_Engelsrufer
 * @module   Engelsrufer
 * Used for getting tags of particular blog
 * Override Blog Helper Mageplaza\Blog\Helper\Data
 * Created by AA 06.05.2019
*/

namespace Pixelmechanics\Engelsrufer\Helper;


use Mageplaza\Blog\Helper\Data as MainHelper;

/**
 * Class Data
 * @package Pixelmechanics\Engelsrufer\Helper
 */
class Blog extends MainHelper
{
     /**
     * get tag collection
     *
     * @param $array
     *
     * @return array|string
     */
    public function getTagCollection($array)
    {
        $collection = $this->getObjectList(self::TYPE_TAG)
            ->addFieldToFilter('tag_id', ['in' => $array]);

        return $collection;
    }
    
    /**
    * get array, int  (category id array, current blog id int)
    * @return collection (related blogs) 
    */
    
     public function getRelatedPost($catIds, $blogId){
        $blogIds = array();
        foreach($catIds as $catid):
            $blogCollection = $this->getPostCollection("category",$catid);
        endforeach;
        return $blogCollection;
    }
    
    /**
    * get blog key (string)
    * @return string (URL) 
    */
    
    public function getPostUrl($urlKey){
         /* get blog route Url defined in configuration GOTO: Shops >> configuration >> Megplaza extension >> Better Blog >> Name der Route */
            $blogRoute =  $this->getRoute(); 
         /* get blog URL suffix defined in configuration GOTO: Shops >> configuration >> Megplaza extension >> Better Blog >> Url Suffix */ 
            $blogSuffix =  $this->getUrlSuffix();  
          return   $blogRoute."/"."post/".$urlKey.$blogSuffix;
    }
}
