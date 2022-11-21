<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup;

use Amasty\Mostviewed\Setup\UpgradeData\CreatePackPage;
use Amasty\Mostviewed\Setup\UpgradeData\UpgradeSettings;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package Amasty\Mostviewed\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var UpgradeSettings
     */
    private $upgradeSettings;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var CreatePackPage
     */
    private $createPackPage;

    public function __construct(
        UpgradeSettings $upgradeSettings,
        CreatePackPage $createPackPage,
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->upgradeSettings = $upgradeSettings;
        $this->logger = $logger;
        $this->appState = $appState;
        $this->createPackPage = $createPackPage;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            [$this, 'upgradeCallback'],
            [$setup, $context]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgradeCallback(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            try {
                $this->upgradeSettings->execute($setup);
            } catch (\Exception $ex) {
                $this->logger->critical(__('Amasty Related Products Settings was not fully converted'));
                $this->logger->critical($ex);
            }
        }

        if (version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->createPackPage->execute($setup);
        }

        $setup->endSetup();
    }
}
