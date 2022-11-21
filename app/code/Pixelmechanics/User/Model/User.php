<?php
/*
 * @author JM by 30.03.21
 * Override the model class to skip the seriable string check for sendcloud
 * Trello: https://trello.com/c/zHx4nbni/129-2020-06-1-update-magento-version-234	
*/

namespace Pixelmechanics\User\Model;

class User extends \Magento\User\Model\User
{
    /**
     * Load user by its username
     *
     * @param string $username
     * @return $this
     */
    public function loadByUsername($username)
    {
        $data = $this->getResource()->loadByUsername($username);
        if ($data !== false) {
            //echo "<pre>"; print_r($data); exit;
            /**
            * Do not serialize the string which is coming from the sendcloud
            * PM JM, 30.03.21, @link - https://trello.com/c/zHx4nbni/129-2020-06-1-update-magento-version-234
            */
            if($data['email'] != "sendcloud@api.com") {
                if (is_string($data['extra'])) {
                    //$data['extra'] = $this->serializer->unserialize($data['extra']);
                }
            }

            $this->setData($data);
            $this->setOrigData();
        }
        return $this;
    }
}