<?php

namespace Amasty\GdprCookie\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpgradeTo210
     */
    private $upgradeTo210;

    /**
     * @var Operation\UpgradeTo220
     */
    private $upgradeTo220;

    /**
     * @var Operation\UpgradeTo230
     */
    private $upgradeTo230;

    /**
     * @var Operation\UpgradeTo240
     */
    private $upgradeTo240;

    /**
     * @var Operation\UpgradeTo241
     */
    private $upgradeTo241;

    /**
     * @var Operation\UpgradeTo270
     */
    private $upgradeTo270;

    /**
     * @var Operation\UpgradeTo280
     */
    private $upgradeTo280;

    public function __construct(
        Operation\UpgradeTo210 $upgradeTo210,
        Operation\UpgradeTo220 $upgradeTo220,
        Operation\UpgradeTo230 $upgradeTo230,
        Operation\UpgradeTo240 $upgradeTo240,
        Operation\UpgradeTo241 $upgradeTo241,
        Operation\UpgradeTo270 $upgradeTo270,
        Operation\UpgradeTo280 $upgradeTo280
    ) {
        $this->upgradeTo210 = $upgradeTo210;
        $this->upgradeTo220 = $upgradeTo220;
        $this->upgradeTo230 = $upgradeTo230;
        $this->upgradeTo240 = $upgradeTo240;
        $this->upgradeTo241 = $upgradeTo241;
        $this->upgradeTo270 = $upgradeTo270;
        $this->upgradeTo280 = $upgradeTo280;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $this->addCookieLifetime($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->upgradeTo210->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->upgradeTo220->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->upgradeTo230->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->upgradeTo240->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.4.1', '<')) {
            $this->upgradeTo241->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.7.0', '<')) {
            $this->upgradeTo270->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.8.0', '<')) {
            $this->upgradeTo280->execute($setup);
        }

        $setup->endSetup();
    }

    protected function addCookieLifetime(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(\Amasty\GdprCookie\Setup\Operation\CreateCookieTable::TABLE_NAME);
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'cookie_lifetime',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => false,
                'default'  => '',
                'comment'  => 'Cookie Lifetime'
            ]
        );

        $connection->dropColumn($table, 'consent_type');
    }
}
