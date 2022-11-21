<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Amasty\GdprCookie\Api\Data\CookieConsentInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo280
{
    /**
     * @var CreateCookieConsentStatusTable
     */
    private $createCookieConsentStatusTable;

    public function __construct(CreateCookieConsentStatusTable $createCookieConsentStatusTable)
    {
        $this->createCookieConsentStatusTable = $createCookieConsentStatusTable;
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createCookieConsentStatusTable->execute($setup);
        $this->addGroupsStatusColumn($setup);
    }

    private function addGroupsStatusColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $consentTable = $setup->getTable(CreateCookieConsentTable::TABLE_NAME);
        $connection->addColumn(
            $consentTable,
            CookieConsentInterface::GROUPS_STATUS,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Groups Status'
            ]
        );
    }
}
