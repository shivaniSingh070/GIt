<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Analytics;

use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Amasty\Mostviewed\Api\AnalyticRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Api\Data\ViewInterface;
use Amasty\Mostviewed\Api\Data\GroupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Collector
 * @package Amasty\Mostviewed\Model\Analytics
 */
class Collector
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AnalyticRepositoryInterface
     */
    private $analyticRepository;

    /**
     * @var string
     */
    private $type;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        AnalyticRepositoryInterface $analyticRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $resourceConnection,
        $type = ''
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceConnection = $resourceConnection;
        $this->analyticRepository = $analyticRepository;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $connection = $this->resourceConnection->getConnection();
        $viewSelect = $connection->select()->from(
            $this->resourceConnection->getTableName('mostviewed_' . $this->type . '_temp'),
            [
                'counter'    => 'count(*)',
                'version_id' => 'max(id)'
            ]
        )->group(ViewInterface::BLOCK_ID);

        $analytics = $this->getAnalytics();
        foreach ($this->loadGroupIds($connection) as $groupId) {
            /** @var Analytic $view */
            $view = isset($analytics[$groupId])
                ?
                $analytics[$groupId]
                :
                $this->analyticRepository->getNew();
            $viewSelect
                ->where('id > ?', $view->getVersionId())
                ->having('block_id = ?', $groupId);
            if ($statistics = $connection->fetchRow($viewSelect)) {
                $view
                    ->setBlockId($groupId)
                    ->setCounter($view->getCounter() + $statistics['counter'])
                    ->setType($this->type)
                    ->setVersionId($statistics['version_id']);
                $this->analyticRepository->save($view);
            }
            $viewSelect->reset(\Zend_Db_Select::WHERE);
            $viewSelect->reset(\Zend_Db_Select::HAVING);
        }
    }

    /**
     * @return array
     */
    private function getAnalytics()
    {
        $analytics = [];

        foreach ($this->loadAnalytics() as $analytic) {
            $analytics[$analytic->getBlockId()] = $analytic;
        }

        return $analytics;
    }

    /**
     * @return Analytic[]
     */
    private function loadAnalytics()
    {
        return $this->analyticRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter(AnalyticInterface::TYPE, $this->type)
                ->create()
        )->getItems();
    }

    /**
     * @param AdapterInterface $connection
     *
     * @return array
     */
    private function loadGroupIds($connection)
    {
        $select = $connection->select()->from(
            $this->resourceConnection->getTableName('amasty_mostviewed_group'),
            GroupInterface::GROUP_ID
        );

        return $connection->fetchCol($select);
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
