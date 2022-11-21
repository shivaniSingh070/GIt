<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Account;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCardAccount\Model\CustomerCard\Repository as CustomerCardRepository;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;

class Remove extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CustomerCardRepository
     */
    private $customerCardRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        CustomerCardRepository $customerCardRepository,
        ConfigProvider $configProvider
    ) {
        parent::__construct($context);
        $this->customerCardRepository = $customerCardRepository;
        $this->session = $session;
        $this->configProvider = $configProvider;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            if (!$this->configProvider->isEnabled()) {
                throw new NotFoundException(__('Invalid Request'));
            }
            if (!$this->session->isLoggedIn()) {
                throw new LocalizedException(__('The session has expired. Please refresh the page.'));
            }

            $accountId = (int)$this->getRequest()->getParam('account_id');
            $currentCustomerId = (int)$this->session->getCustomerId();
            $model = $this->customerCardRepository->getByAccountAndCustomerId($accountId, $currentCustomerId);

            if ($model->getCustomerId() == $currentCustomerId) {
                $this->customerCardRepository->delete($model);
                $response = ['message' => __('Gift Card was successfully removed.'), 'error' => false];
            } else {
                $response = ['message' => __('Specified Gift Card for current user is not found.'), 'error' => true];
            }
        } catch (NotFoundException | LocalizedException $e) {
            $response = [
                'message' => $e->getMessage(),
                'error' => true
            ];
        } catch (\Exception $e) {
            $response = ['message' => __('Cannot remove gift card.'), 'error' => true];
        }

        return $resultJson->setData($response);
    }
}
