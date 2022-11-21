<?php
/**
 * Override getRobots functions of Magento\Framework\View\Page\Config to add "NOINDEX,NOFOLLOW" in category page if filter is applied
 * Updated by AA on 25.11.2019
 * https://trello.com/c/W6c5ckeI/295-bug-seobility-and-deeptrawl-registrate-too-many-sites-on-engelsrufer#comment-5ddb7cc24ccc0577d066ff7f
 */


namespace Pixelmechanics\Engelsrufer\View\Page;

use Magento\Framework\App;
use Magento\Framework\App\Area;
use Magento\Framework\Escaper;
use Magento\Framework\View;
use Magento\Framework\View\Page\Title;

class Config extends \Magento\Framework\View\Page\Config
{

    /*
     * \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /*
     * \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * @var \Magento\Framework\App\State
     */
    private $areaResolver;

    /**
     * @var bool
     */
    private $isIncludesAvailable;
    /**
     * @var App\Request\Http
     */
    private $_request;

    /**
     * This getter serves as a workaround to add this dependency to this class without breaking constructor structure.
     *
     * @return \Magento\Framework\App\State
     *
     * @deprecated 100.0.7
     */
    private function getAreaResolver()
    {
        if ($this->areaResolver === null) {
            $this->areaResolver = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\State::class);
        }
        return $this->areaResolver;
    }

    /**
     * @param View\Asset\Repository $assetRepo
     * @param View\Asset\GroupedCollection $pageAssets
     * @param App\Config\ScopeConfigInterface $scopeConfig
     * @param View\Page\FaviconInterface $favicon
     * @param Title $title
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param bool $isIncludesAvailable
     * @param Escaper|null $escaper
     * @param App\Request\Http $request
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     */
    public function __construct(
        View\Asset\Repository $assetRepo,
        View\Asset\GroupedCollection $pageAssets,
        App\Config\ScopeConfigInterface $scopeConfig,
        View\Page\FaviconInterface $favicon,
        Title $title,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $isIncludesAvailable = true,
        Escaper $escaper = null,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    )
    {
        $this->_request = $request;
        $this->layerResolver = $layerResolver;
        /*
         * Added call to parent constructor which was causing escaper param to be left uninitialized
         * PM AY 28 Sept 2021
         */
        parent::__construct($assetRepo, $pageAssets, $scopeConfig, $favicon, $title, $localeResolver, $isIncludesAvailable, $escaper);
    }

    /*
     * Override the getRobots function
     * called function around the core function
     * checked if page is category and filter is applied then return "NOINDEX,NOFOLLOW"
     * If page is not category then return the original set robots
     */
    public function getRobots()
    {

        if ($this->getAreaResolver()->getAreaCode() !== Area::AREA_FRONTEND) {
            return 'NOINDEX,NOFOLLOW';
        }
        $checkModule = $this->_request->getFullActionName();
        if ($checkModule == 'catalog_category_view') {
            $layer = $this->layerResolver->get();
            $activeFilters = $layer->getState()->getFilters();
            if (is_array($activeFilters) && count($activeFilters) > 0) {
                return 'NOINDEX,NOFOLLOW';
            }
        }
        $this->build();
        if (empty($this->metadata[self::META_ROBOTS])) {
            $this->metadata[self::META_ROBOTS] = $this->scopeConfig->getValue(
                'design/search_engine_robots/default_robots',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->metadata[self::META_ROBOTS];
    }
}
