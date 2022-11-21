<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Plugin\Mui;

use Amasty\Mostviewed\Controller\Adminhtml\Product\Mui\Render as RenderController;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Render
 * @package Amasty\Mostviewed\Plugin\Mui
 */
class Render
{
    /**
     * @var \Amasty\Mostviewed\Model\Repository\GroupRepository
     */
    private $groupRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Amasty\Mostviewed\Model\Repository\GroupRepository $groupRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->groupRepository = $groupRepository;
        $this->registry = $registry;
    }

    /**
     * @param RenderController $renderController
     */
    public function beforeExecute(RenderController $renderController)
    {
        $request = $renderController->getRequest();
        if ($conditions = $request->getParam('rule', null)) {
            $relation = $request->getParam('relation') . '_show';
            $group = $this->getGroup($request);

            $group->setRelation($relation);
            $group->setShowForOutOfStock($request->getParam('for_out_of_stock', 0));
            $group->loadPost($conditions);

            $this->registry->register(
                \Amasty\Mostviewed\Ui\DataProvider\Product\ProductDataProvider::PRODUCTS_KEY,
                $group->getMatchingProductIdsByGroup()
            );
        }
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface|\Amasty\Mostviewed\Model\Group
     */
    protected function getGroup($request)
    {
        try {
            /** @var \Amasty\Mostviewed\Model\Group $group */
            $group = $this->groupRepository->getById($request->getParam('group_id'));
        } catch (NoSuchEntityException $entityException) {
            $group = $this->groupRepository->getNew();
            $group->setStores('0');
        }

        return $group;
    }
}
