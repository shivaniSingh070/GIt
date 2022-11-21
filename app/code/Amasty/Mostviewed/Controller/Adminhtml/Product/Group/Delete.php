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
 * Class Delete
 * @package Amasty\Mostviewed\Controller\Adminhtml\Product\Group
 */
class Delete extends Action
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

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\GroupRepository $groupRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $groupId = (int)$this->getRequest()->getParam('id');
        if ($groupId) {
            try {
                $this->groupRepository->deleteById($groupId);
                $this->messageManager->addSuccessMessage(__('The rule have been deleted.'));
                $this->_redirect('amasty_mostviewed/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('amasty_mostviewed/*/edit', ['id' => $groupId]);

                return;
            }
        }
        $this->_redirect('amasty_mostviewed/*/');
    }
}
