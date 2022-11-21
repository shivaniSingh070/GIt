<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;

class UpdateDataTo240
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        State $appState,
        ResourceConnection $resource
    ) {
        $this->appState = $appState;
        $this->resource = $resource;
    }

    /**
     * @throws \Exception
     */
    public function upgrade()
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'updateModuleData']);
    }

    public function updateModuleData()
    {
        $this->updateCookie();
        $this->updateCookieStoreData();
    }

    private function updateCookie()
    {
        $connection = $this->resource->getConnection();
        $cookieTable = $this->resource->getTableName(CreateCookieTable::TABLE_NAME);
        $cookieGroupLinkTable = $this->resource->getTableName('amasty_gdprcookie_cookie_group_link');
        $selectUpdate = $connection->select()
            ->joinLeft(
                ['gl' => $cookieGroupLinkTable],
                'c.id = gl.cookie_id',
                ['group_id']
            );
        $queryUpdate = $connection->updateFromSelect($selectUpdate, ['c' => $cookieTable]);
        $connection->query($queryUpdate);
        $connection->dropTable($cookieGroupLinkTable);
    }

    private function updateCookieStoreData()
    {
        $connection = $this->resource->getConnection();
        $cookieStoreDataTable = $this->resource->getTableName(CreateCookieStoreTable::TABLE_NAME);
        $cookieGroupLinkStoreTable = $this->resource->getTableName('amasty_gdprcookie_cookie_group_link_store');
        $selectUpdate = $connection->select()
            ->joinLeft(
                ['ls' => $cookieGroupLinkStoreTable],
                's.cookie_id = ls.cookie_id AND s.store_id = ls.store_id',
                ['group_id']
            );
        $queryUpdate = $connection->updateFromSelect($selectUpdate, ['s' => $cookieStoreDataTable]);
        $connection->query($queryUpdate);
        $connection->dropTable($cookieGroupLinkStoreTable);
    }
}
