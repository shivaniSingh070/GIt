<?php

namespace Amasty\AdminActionsLog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Operation\UpdateDataTo200
     */
    private $updateDataTo200;

    public function __construct(
        Operation\UpdateDataTo200 $updateDataTo200
    ) {
        $this->updateDataTo200 = $updateDataTo200;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->updateDataTo200->upgrade($setup);
        }
    }
}
