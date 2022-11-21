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
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class InlineEdit
 * @package Amasty\Mostviewed\Controller\Adminhtml\Product\Group
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Mostviewed::rule';

    /**
     * @var \Amasty\Mostviewed\Model\Repository\GroupRepository
     */
    private $groupRepository;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\GroupRepository $groupRepository
    ) {
        parent::__construct($context);
        $this->groupRepository = $groupRepository;
    }

    /**
     * Inline edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if ($this->getRequest()->getParam('isAjax') && count($postItems)) {
            foreach ($postItems as $itemId => $itemData) {
                /** @var Group $model */
                $model = $this->groupRepository->getById($itemId);
                try {
                    $model->addData($itemData);
                    $this->groupRepository->save($model);
                } catch (LocalizedException $e) {
                    $messages[] = $e->getMessage();
                    $error = true;
                } catch (\Exception $e) {
                    $messages[] = __('Something went wrong while saving the item.');
                    $error = true;
                }
            }
        } else {
            $messages[] = __('Please correct the data sent.');
            $error = true;
        }

        return $resultJson->setData(
            [
                'messages' => $messages,
                'error'    => $error
            ]
        );
    }
}
