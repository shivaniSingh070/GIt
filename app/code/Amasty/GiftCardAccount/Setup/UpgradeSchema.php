<?php

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\AddRecipientAccountColumn
     */
    private $addRecipientAccountColumn;

    /**
     * @var Operation\CreateGiftCardAccountTransactionTable
     */
    private $createGiftCardAccountTransactionTable;

    /**
     * @var Operation\AddRedeemableAccountColumn
     */
    private $addRedeemableAccountColumn;

    /**
     * @var Operation\AddUsageAccountColumn
     */
    private $addUsageAccountColumn;

    /**
     * @var Operation\AddPhoneAccountColumn
     */
    private $addPhoneAccountColumn;

    public function __construct(
        Operation\AddRecipientAccountColumn $addRecipientAccountColumn,
        Operation\CreateGiftCardAccountTransactionTable $createGiftCardAccountTransactionTable,
        Operation\AddRedeemableAccountColumn $addRedeemableAccountColumn,
        Operation\AddUsageAccountColumn $addUsageAccountColumn,
        Operation\AddPhoneAccountColumn $addPhoneAccountColumn
    ) {
        $this->addRecipientAccountColumn = $addRecipientAccountColumn;
        $this->createGiftCardAccountTransactionTable = $createGiftCardAccountTransactionTable;
        $this->addRedeemableAccountColumn = $addRedeemableAccountColumn;
        $this->addUsageAccountColumn = $addUsageAccountColumn;
        $this->addPhoneAccountColumn = $addPhoneAccountColumn;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addRecipientAccountColumn->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->createGiftCardAccountTransactionTable->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.4.0', '<')) {
            $this->addRedeemableAccountColumn->execute($setup);
            $this->addUsageAccountColumn->execute($setup);
            $this->addPhoneAccountColumn->execute($setup);
        }

        $setup->endSetup();
    }
}
