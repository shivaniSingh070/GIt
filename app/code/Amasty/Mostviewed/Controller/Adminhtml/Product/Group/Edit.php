<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Adminhtml\Product\Group;

use Amasty\Mostviewed\Model\Group;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Edit
 * @package Amasty\Mostviewed\Controller\Adminhtml\Product\Group
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Mostviewed::rule';

    const CURRENT_GROUP = 'amasty_mostviewed_product_group';

    /**
     * @var \Amasty\Mostviewed\Model\Repository\GroupRepository
     */
    private $groupRepository;

    /**
     * @var \Amasty\Mostviewed\Model\GroupFactory
     */
    private $groupFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\GroupRepository $groupRepository,
        \Amasty\Mostviewed\Model\GroupFactory $groupFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
        $this->coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $groupId = (int)$this->getRequest()->getParam('id');
        if ($groupId) {
            try {
                $model = $this->groupRepository->getById($groupId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('*/*/index');

                return;
            }
        } else {
            /** @var Group $model */
            $model = $this->groupFactory->create();
        }

        $this->applyFormName($model);

        // set entered data if was error when we do save
        $data = $this->dataPersistor->get(GROUP::PERSISTENT_NAME);
        if (!empty($data)) {
            $model->addData($data);
        }

        if (!is_array($model->getCategoryIds())) {
            $model->setCategoryIds(explode(',', $model->getCategoryIds()));
        }

        $this->coreRegistry->register(self::CURRENT_GROUP, $model);
        $this->initAction();

        // set title and breadcrumbs
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Related Product Rule'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getGroupId() ?
                __('Edit Related Product Rule # %1', $model->getGroupId())
                : __('New Related Product Rule')
        );

        $breadcrumb = $model->getGroupId() ?
            __('Edit Related Product Rule # %1', $model->getGroupId())
            : __('New Related Product Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    private function initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE)
            ->_addBreadcrumb(__('Related Product Rules'), __('Related Product Rules'));

        return $this;
    }

    /**
     * @param Group $model
     */
    private function applyFormName(Group &$model)
    {
        $model->getWhereConditions()->setFormName(Group::FORM_NAME);
        $model->getWhereConditions()->setJsFormObject(
            $model->getWhereConditionsFieldSetId(Group::FORM_NAME)
        );
        $model->getWhereConditions()->setRuleFactory($this->groupFactory);

        $model->getConditions()->setFormName(Group::FORM_NAME);
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId(Group::FORM_NAME)
        );
        $model->getConditions()->setRuleFactory($this->groupFactory);

        $model->getSameAsConditions()->setFormName(Group::FORM_NAME);
        $model->getSameAsConditions()->setJsFormObject(
            $model->getSameAsConditionsFieldSetId(Group::FORM_NAME)
        );
        $model->getSameAsConditions()->setRuleFactory($this->groupFactory);
    }
}
