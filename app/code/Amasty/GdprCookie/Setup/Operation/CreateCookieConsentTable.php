<?php

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\GdprCookie\Api\Data\CookieConsentInterface;

class CreateCookieConsentTable
{
    public const TABLE_NAME = 'amasty_gdprcookie_cookie_consents';

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $customerTable = $setup->getTable('customer_entity');
        $websiteTable = $setup->getTable('store_website');

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty GDPR Table with cookie consent customers'
            )->addColumn(
                CookieConsentInterface::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Id'
            )->addColumn(
                CookieConsentInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Customer Id'
            )->addColumn(
                CookieConsentInterface::DATE_RECIEVED,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ],
                'Date Recieved'
            )->addColumn(
                CookieConsentInterface::CONSENT_STATUS,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Consent Status'
            )->addColumn(
                CookieConsentInterface::WEBSITE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true
                ],
                'Website'
            )->addColumn(
                CookieConsentInterface::CUSTOMER_IP,
                Table::TYPE_TEXT,
                15,
                [
                    'nullable' => false
                ],
                'Customer Ip Address'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    CookieConsentInterface::CUSTOMER_ID,
                    $customerTable,
                    'entity_id'
                ),
                CookieConsentInterface::CUSTOMER_ID,
                $customerTable,
                'entity_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    CookieConsentInterface::WEBSITE,
                    $websiteTable,
                    'website_id'
                ),
                CookieConsentInterface::WEBSITE,
                $websiteTable,
                'website_id',
                Table::ACTION_CASCADE
            );
    }
}
