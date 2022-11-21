<?php
/**
 * @author: NA
 * @date: 8th jul 2019
 * @description: Opengraph Block class to add the Store Logo from the store configuration.
*/

namespace Pixelmechanics\Engelsrufer\Block\Cms;

class Opengraph extends \Magento\Framework\View\Element\Template
{

	/**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    protected $_logo;

    protected $_pageTitle;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Pixelmechanics\Engelsrufer\Helper\Data
     */
    protected $_pmhelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\Framework\Filesystem $filesystem,
        \Pixelmechanics\Engelsrufer\Helper\Data $pmhelper,
        array $data = []
    )
	{
		$this->_logo = $logo;
        $this->_pageTitle = $pageTitle;
        $this->_filesystem = $filesystem;
        $this->_pmhelper = $pmhelper;
        parent::__construct($context);
	}


    public function getLogo(){
        
        $ogLogo = $this->_pmhelper->getStoreConfig('opengraph_configuration/head/oglogo');
        $baseDir = $this->getBaseUrl().'pub/media/opengraph/';

        return $baseDir.$ogLogo;

    }
     

    public function getTitle()
    {
        return $this->_pageTitle->getShort();
    }

}