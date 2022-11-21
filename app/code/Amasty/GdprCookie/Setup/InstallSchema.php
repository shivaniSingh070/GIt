<?php

namespace Amasty\GdprCookie\Setup;

use Amasty\GdprCookie\Setup\Operation\CreateCookieTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupsTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieStoreTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupStoreTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupLinkTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieConsentTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateCookieTable
     */
    private $cookieTable;

    /**
     * @var CreateCookieGroupsTable
     */
    private $cookieGroupsTable;

    /**
     * @var CreateCookieStoreTable
     */
    private $cookieStoreTable;

    /**
     * @var CreateCookieGroupStoreTable
     */
    private $cookieGroupStoreTable;

    /**
     * @var CreateCookieGroupLinkTable
     */
    private $cookieGroupLinkTable;

    /**
     * @var CreateCookieConsentTable
     */
    private $cookieConsentTable;

    public function __construct(
        CreateCookieTable $cookieTable,
        CreateCookieGroupsTable $cookieGroupsTable,
        CreateCookieStoreTable $cookieStoreTable,
        CreateCookieGroupStoreTable $cookieGroupStoreTable,
        CreateCookieGroupLinkTable $cookieGroupLinkTable,
        CreateCookieConsentTable $cookieConsentTable
    ) {
        $this->cookieTable = $cookieTable;
        $this->cookieGroupsTable = $cookieGroupsTable;
        $this->cookieStoreTable = $cookieStoreTable;
        $this->cookieGroupStoreTable = $cookieGroupStoreTable;
        $this->cookieGroupLinkTable = $cookieGroupLinkTable;
        $this->cookieConsentTable = $cookieConsentTable;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->cookieTable->execute($setup);
        $this->cookieGroupsTable->execute($setup);
        $this->cookieStoreTable->execute($setup);
        $this->cookieGroupStoreTable->execute($setup);
        $this->cookieGroupLinkTable->execute($setup);
        $this->cookieConsentTable->execute($setup);

        $setup->endSetup();
    }
}
