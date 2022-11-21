<?php
namespace Pixelmechanics\MarkerIO\Block\Index;
class Marker extends \Magento\Framework\View\Element\Template
{
    protected $scopeConfig;
    protected $storeManager;

	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
	{
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
		parent::__construct($context);
	}

	public function getMarkerIOSnippet()
	{
        return $this->scopeConfig->getValue(
            'marker_io/general/marker_io_snippet',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
	}
}