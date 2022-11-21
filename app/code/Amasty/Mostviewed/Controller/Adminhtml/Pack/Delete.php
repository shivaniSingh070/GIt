<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Magento\Backend\App\Action;

/**
 * Class Delete
 * @package Amasty\Mostviewed\Controller\Adminhtml\Pack
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Mostviewed::pack';

    /**
     * @var \Amasty\Mostviewed\Model\Repository\PackRepository
     */
    private $packRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\PackRepository $packRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->packRepository = $packRepository;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $packId = (int)$this->getRequest()->getParam('id');
        if ($packId) {
            try {
                $this->packRepository->deleteById($packId);
                $this->messageManager->addSuccessMessage(__('The pack have been deleted.'));
                $this->_redirect('amasty_mostviewed/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('amasty_mostviewed/*/edit', ['id' => $packId]);

                return;
            }
        }
        $this->_redirect('amasty_mostviewed/*/');
    }
}
