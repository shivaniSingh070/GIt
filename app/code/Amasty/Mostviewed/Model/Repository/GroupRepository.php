<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Repository;

use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Amasty\Mostviewed\Model\GroupFactory;
use Amasty\Mostviewed\Model\ResourceModel\Group as GroupResource;
use Amasty\Mostviewed\Model\ResourceModel\Group\CollectionFactory;
use Amasty\Mostviewed\Model\ResourceModel\Group\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupRepository implements GroupRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var GroupResource
     */
    private $groupResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $groups;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var \Amasty\Mostviewed\Model\ResourceModel\RuleIndexFactory
     */
    private $ruleIndexFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Amasty\Mostviewed\Model\Layout\Updater
     */
    private $layoutUpdater;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        GroupFactory $groupFactory,
        GroupResource $groupResource,
        CollectionFactory $groupCollectionFactory,
        \Amasty\Mostviewed\Model\ResourceModel\RuleIndexFactory $ruleIndexFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        SortOrderBuilder $sortOrderBuilder,
        \Amasty\Mostviewed\Model\Layout\Updater $layoutUpdater
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->groupFactory = $groupFactory;
        $this->groupResource = $groupResource;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->ruleIndexFactory = $ruleIndexFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->sessionFactory = $sessionFactory;
        $this->layoutUpdater = $layoutUpdater;
    }

    /**
     * @inheritdoc
     */
    public function getGroupByIdAndPosition($entityId, $position)
    {
        $group = false;
        $groups = [];
        $groupIds = $this->ruleIndexFactory->create()
            ->getGroupByIdAndPosition($entityId, $position);

        if ($groupIds) {
            $this->searchCriteriaBuilder->addFilter(GroupInterface::GROUP_ID, $groupIds, 'in');
            /** @var SortOrder $sortOrder */
            $sortOrder = $this->sortOrderBuilder->setField(GroupInterface::PRIORITY)
                ->setDirection(SortOrder::SORT_ASC)
                ->create();
            $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
            $groups = $this->getList($this->searchCriteriaBuilder->create())->getItems();
        }
        
        while (true) {
            if ($groups) {
                $group = array_shift($groups);
            } else {
                break;
            }

            if ($group = $this->validateGroup($group)) {
                break;
            }
        }

        return $group;
    }

    /**
     * @param GroupInterface $group
     *
     * @return GroupInterface|false
     */
    public function validateGroup($group)
    {
        /** @var GroupInterface|false $group */
        if ($group) {
            $currentCustomerGroup = $this->getCustomerSession()->getCustomerGroupId() ? : 0;
            $customerGroups = $group->getCustomerGroupIds();
            $customerGroups = explode(',', $customerGroups);
            if (!in_array($currentCustomerGroup, $customerGroups)) {
                $group = false;
            }
        }

        return $group;
    }

    /**
     * @inheritdoc
     */
    public function save(GroupInterface $group)
    {
        try {
            if ($group->getGroupId()) {
                $group = $this->getById($group->getGroupId())->addData($group->getData());
            }
            $this->groupResource->save($group);
            unset($this->groups[$group->getGroupId()]);
        } catch (\Exception $e) {
            if ($group->getGroupId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save group with ID %1. Error: %2',
                        [$group->getGroupId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new group. Error: %1', $e->getMessage()));
        }

        return $group;
    }

    /**
     * @inheritdoc
     */
    public function getById($groupId)
    {
        if (!isset($this->groups[$groupId])) {
            /** @var \Amasty\Mostviewed\Model\Group $group */
            $group = $this->groupFactory->create();
            $this->groupResource->load($group, $groupId);
            if (!$group->getGroupId()) {
                throw new NoSuchEntityException(__('Group with specified ID "%1" not found.', $groupId));
            }
            $this->groups[$groupId] = $group;
        }

        return $this->groups[$groupId];
    }

    /**
     * @inheritdoc
     */
    public function delete(GroupInterface $group)
    {
        try {
            $this->layoutUpdater->delete($group->getLayoutUpdateId());
            $this->groupResource->delete($group);
            unset($this->groups[$group->getGroupId()]);
        } catch (\Exception $e) {
            if ($group->getGroupId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove group with ID %1. Error: %2',
                        [$group->getGroupId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove group. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($groupId)
    {
        $groupModel = $this->getById($groupId);
        $this->delete($groupModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Mostviewed\Model\ResourceModel\Group\Collection $groupCollection */
        $groupCollection = $this->groupCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $groupCollection);
        }
        $searchResults->setTotalCount($groupCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $groupCollection);
        }
        $groupCollection->setCurPage($searchCriteria->getCurrentPage());
        $groupCollection->setPageSize($searchCriteria->getPageSize());
        $groups = [];
        /** @var GroupInterface $group */
        foreach ($groupCollection->getItems() as $group) {
            $groups[] = $this->getById($group->getId());
        }
        $searchResults->setItems($groups);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $groupCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $groupCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $groupCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $groupCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $groupCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $groupCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function duplicate($id)
    {
        $model = $this->getById($id);
        $model->setId(null);
        $model->setStatus(0);
        $model->setLayoutUpdateId(null);

        $this->save($model);

        return $this;
    }

    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }

    /**
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNew()
    {
        return $this->groupFactory->create();
    }
}
