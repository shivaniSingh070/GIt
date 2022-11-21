<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeData;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class CreatePackPage
 * @package Amasty\Mostviewed\Setup\UpgradeData
 */
class CreatePackPage
{
    const IDENTIFIER = 'bundles';

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $reinitableConfig;

    public function __construct(
        WriterInterface $configWriter,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
    ) {
        $this->pageFactory = $pageFactory;
        $this->configWriter = $configWriter;
        $this->urlFinder = $urlFinder;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $identifier = $this->getIdentifier();
        // @codingStandardsIngoreStart
        $content = '<h2><strong>Searching for special deals? Browse the list below to find the offer you\'re looking' .
            ' for!</strong></h2><p></p>
            <p>{{widget type="Amasty\Mostviewed\Block\Widget\PackList" columns="3" 
            template="bundle/list.phtml"}}</p>';
        // @codingStandardsIngoreEnd
        $page = $this->pageFactory->create();
        $page->setTitle('All Bundle Packs Page')
            ->setIdentifier($identifier)
            ->setData('mageworx_hreflang_identifier', 'en-us')
            ->setData('amasty_hreflang_uuid', 'en-us')
            ->setData('mp_exclude_sitemap', '1')
            ->setIsActive(false)
            ->setPageLayout('1column')
            ->setStores([0])
            ->setContent($content)
            ->save();

        $this->configWriter->save(\Amasty\Mostviewed\Helper\Config::BUNDLE_PAGE_PATH, $identifier);
        $this->reinitableConfig->reinit();
    }

    /**
     * @param int $index
     * @return string
     */
    private function getIdentifier($index = 0)
    {
        $identifier = self::IDENTIFIER;
        if ($index) {
            $identifier .= '_' . $index;
        }

        $rewrite = $this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => $identifier]);
        if ($rewrite !== null) {
            return $this->getIdentifier(++$index);
        }

        return $identifier;
    }
}
