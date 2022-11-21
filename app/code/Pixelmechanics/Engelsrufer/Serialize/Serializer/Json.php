<?php
/**
 * Extended by N.A on 16.06.2020 
 * @link: https://trello.com/c/EY7iwcFy/135-live-shop-down-utf-8-format-missing
 * UTF8 issue on Live
 */
namespace Pixelmechanics\Engelsrufer\Serialize\Serializer;

/**
 * Serialize data to JSON, unserialize JSON encoded data
 *
 * @api
 * @since 101.0.0
 */
class Json extends \Magento\Framework\Serialize\Serializer\Json
{
    public function utf8ize( $mixed ) {
        if (is_array($mixed)) foreach ($mixed as $key => $value) $mixed[$key] = $this->utf8ize($value);
        elseif (is_string($mixed)) return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        return $mixed;
    }
    
    public function serialize($data){
        $result = json_encode( $this->utf8ize( $data ) );
        if (false === $result) {
            throw new \InvalidArgumentException("Unable to serialize value. Error: " . json_last_error_msg());
        }
        return $result;
    }

    /**
     * @inheritDoc
     * @since 101.0.0
     */
    public function unserialize($string)
    {
        $result = json_decode($string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if(false !== @unserialize($string)){
                return unserialize($string);
            }
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        return $result;
    }
}
