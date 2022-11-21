<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\DesignInterface;

/**
 * Class Config
 * @package Amasty\Mostviewed\Helper
 */
class Config extends AbstractHelper
{
    const MODULE_PATH = 'ammostviewed/';

    const DEFAULT_GATHERED_PERIOD = 30;

    const BUNDLE_PAGE_PATH = 'ammostviewed/bundle_packs/cms_page';

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Amasty\Mostviewed\Model\Rule\Condition\CombineFactory
     */
    private $combineFactory;

    /**
     * @var \Amasty\Mostviewed\Model\Rule\Condition\SameAsCombineFactory
     */
    private $sameAsCombineFactory;

    /**
     * @var \Amasty\Mostviewed\Model\Indexer\RuleProcessor
     */
    private $ruleProcessor;

    /**
     * inject objects for prevent fatal on cloud
     */
    public function __construct(
        \Amasty\Mostviewed\Model\Rule\Condition\CombineFactory $combineFactory,
        \Amasty\Mostviewed\Model\Rule\Condition\SameAsCombineFactory $sameAsCombineFactory,
        \Amasty\Mostviewed\Model\Indexer\RuleProcessor $ruleProcessor,
        \Magento\Framework\Filter\FilterManager $filterManager,
        Context $context
    ) {
        parent::__construct($context);
        $this->filterManager = $filterManager;
        $this->combineFactory = $combineFactory;
        $this->sameAsCombineFactory = $sameAsCombineFactory;
        $this->ruleProcessor = $ruleProcessor;
    }

    /**
     * @param $path
     * @param int $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_PATH . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return int
     */
    public function getGatheredPeriod()
    {
        $period = $this->getModuleConfig('general/period');
        if (!$period) {
            $period = self::DEFAULT_GATHERED_PERIOD;
        }

        return $period;
    }

    /**
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->getModuleConfig('general/order_status');
    }

    /**
     * @return bool
     */
    public function isMessageInCartEnabled()
    {
        return (bool)$this->getModuleConfig('bundle_packs/display_cart_message');
    }

    /**
     * @return bool
     */
    public function isBlockInCartEnabled()
    {
        return (bool)$this->getModuleConfig('bundle_packs/display_cart_block');
    }

    /**
     * @return string
     */
    public function getBlockPosition()
    {
        return $this->getModuleConfig('bundle_packs/position');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->filterManager->stripTags(
            $this->getModuleConfig('bundle_packs/tab_title'),
            [
                'allowableTags' => null,
                'escape' => true
            ]
        );
    }

    /**
     * @param null|int $storeId
     *
     * @return int
     */
    public function getThemeForStore($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            DesignInterface::XML_PATH_THEME_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
