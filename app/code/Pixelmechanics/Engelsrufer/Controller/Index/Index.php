<?php

/* Define controller
 * Created by AA 24.04.2019
 * Updated by NA 18.06.19 to created a newsletter separate page. 
 */
namespace Pixelmechanics\Engelsrufer\Controller\Index;
 
use Magento\Framework\App\Action\Context;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
 
   //Define contructor 

    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    public function execute()
    { 
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sign up for the newsletter'));
        return $resultPage;
    }
}
