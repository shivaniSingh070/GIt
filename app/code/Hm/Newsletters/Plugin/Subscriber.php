<?php
namespace Hm\Newsletters\Plugin;

use Magento\Framework\App\Request\Http;

class Subscriber {
    protected $request;
    public function __construct(Http $request){
        $this->request = $request;
    }

    public function aroundSubscribe($subject, \Closure $proceed, $email) {
        if ($this->request->isPost()) { 

            $firstname = $this->request->getPost('firstname');
            $lastname = $this->request->getPost('lastname');

            $subject->setCFirstname($firstname);
            $subject->setCLastname($lastname);
            $result = $proceed($email);

            try {
                $subject->save();
            }catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
        return $result;
    }
}