<?php

namespace Amasty\GdprCookie\Setup;

use Amasty\GdprCookie\Model\CookieFactory;
use Amasty\GdprCookie\Model\Repository\CookieRepository;
use Amasty\GdprCookie\Setup\Operation\InstallCookieData;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CookieFactory
     */
    private $cookieFactory;

    /**
     * @var CookieRepository
     */
    private $cookieRepository;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var InstallCookieData
     */
    private $installCookieData;

    public function __construct(
        PageFactory $pageFactory,
        ScopeConfigInterface $scopeConfig,
        CookieFactory $cookieFactory,
        CookieRepository $cookieRepository,
        State $appState,
        InstallCookieData $installCookieData
    ) {
        $this->pageFactory = $pageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->cookieFactory = $cookieFactory;
        $this->cookieRepository = $cookieRepository;
        $this->appState = $appState;
        $this->installCookieData = $installCookieData;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'installModuleData']);
    }

    /**
     * @return void
     */
    public function installModuleData()
    {
        $this->installCmsPagesData();
        $this->installCookieData->addCookieInformation();

        // copy cookies data from Amasty_Gdpr
        if ($gdprConfigCookies = $this->scopeConfig->getValue('amasty_gdpr/cookie_policy/confirmation_cookies')) {
            $cookies = preg_split('/\n|\r\n?/', $gdprConfigCookies);

            foreach ($cookies as $cookieName) {
                $cookie = $this->cookieFactory->create();
                $cookie->setName($cookieName);
                $cookie->setDescription('Cookie from GDPR');

                try {
                    $this->cookieRepository->save($cookie);
                } catch (\Exception $e) {
                    null;//do nothing
                }
            }
        }
    }

    private function installCmsPagesData()
    {
        $page = $this->pageFactory->create();

        if (!$page->checkIdentifier('cookie-settings', 0)) {
            try {
                $page->setTitle('Cookie Settings')
                    ->setIdentifier('cookie-settings')
                    ->setIsActive(true)
                    ->setPageLayout('1column')
                    ->setContent(
                        '{{widget type="Amasty\GdprCookie\Block\Widget\Settings" ' .
                        'type_name="Amasty Cookie Settings"}}'
                    )->setStoreId(["0"])
                    ->save();
            } catch (\Exception $e) {
                null;
            }
        }

        if (!$page->checkIdentifier('cookie-policy', 0)) {
            try {
                $page = $this->pageFactory->create();

                $page->setTitle('Cookie Policy')
                    ->setIdentifier('cookie-policy')
                    ->setContent($this->getCookiePolicyContent())
                    ->setPageLayout('1column')
                    ->setStoreId(["0"])
                    ->save();
            } catch (\Exception $e) {
                null;
            }
        }
    }

    /**
     * @return string
     */
    private function getCookiePolicyContent()
    {
        return '<p>This site, like many others, uses small files called cookies to help us customize your experience.
                    Find out more about cookies and how you can control them.</p>
                    <p></p>
                    <p>What is a cookie?</p>
                    <p></p>
                    <p>A cookie is a small file that can be placed on your device that allows us
                    to recognise and remember you. It is sent to your browser and stored on your computer’s
                    hard drive or tablet or mobile device. When you visit our sites, we may collect information
                    from you automatically through cookies or similar technology.</p>
                    <p></p>
                    <p>How do we use cookies?</p>
                    <p></p>
                    <p>We use cookies in a range of ways to improve your experience on our site, including:</p>
                    <p></p>
                    <p>Keeping you signed in;</p>
                    <p>Understanding how you use our site;</p>
                    <p>Showing you content that is relevant to you;</p>
                    <p>Showing you products and services that are relevant to you.;</p>';
    }
}
