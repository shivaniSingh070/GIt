<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Amasty\GdprCookie\Api\Data\CookieConsentInterface;
use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateCookieConsentStatusTable
{
    public const TABLE_NAME = 'amasty_gdprcookie_cookie_consent_status';

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
        $cookieConsentTable = $setup->getTable(CreateCookieConsentTable::TABLE_NAME);
        $cookieGroupsTable = $setup->getTable(CreateCookieGroupsTable::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty GDPR Table with cookie consent status'
            )->addColumn(
                'cookie_consents_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Cookie Consent Id'
            )->addColumn(
                'group_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Cookie Group Id'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Consent Status'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    'cookie_consents_id',
                    $cookieConsentTable,
                    CookieConsentInterface::ID
                ),
                'cookie_consents_id',
                $cookieConsentTable,
                CookieConsentInterface::ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    'group_id',
                    $cookieGroupsTable,
                    CookieGroupsInterface::ID
                ),
                'group_id',
                $cookieGroupsTable,
                CookieGroupsInterface::ID,
                Table::ACTION_CASCADE
            );
    }
}
