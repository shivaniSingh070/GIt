<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreateAccount implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Repository
     */
    private $accountRepository;

    public function __construct(
        ManagerInterface $eventManager,
        Repository $accountRepository
    ) {
        $this->eventManager = $eventManager;
        $this->accountRepository = $accountRepository;
    }

    public function execute(Observer $observer)
    {
        $data = $observer->getEvent()->getAccountData();
        /** @var GiftCardAccountInterface $model */
        $model = $this->accountRepository->getEmptyAccountModel()
            ->setStatus(AccountStatus::STATUS_ACTIVE)
            ->setOrderItemId((int)$data->getOrderItemId())
            ->setInitialValue((float)$data->getInitialValue())
            ->setCurrentValue((float)$data->getCurrentValue())
            ->setWebsiteId((int)$data->getWebsiteId())
            ->setImageId((int)$data->getImageId())
            ->setDeliveryDate($data->getDateDelivery())
            ->setExpiredDate($data->getExpiredDate())
            ->setCustomerCreatedId($data->getCustomerCreatedId())
            ->setIsSent(false)
            ->setCodePool((int)$data->getCodePool())
            ->setRecipientPhone((string)$data->getMobilenumber())
            ->setRecipientEmail((string)$data->getRecipientEmail());

        $this->eventManager->dispatch(
            'amasty_giftcard_account_create_before_save',
            ['account_data' => $data, 'account' => $model]
        );

        $this->accountRepository->save($model);

        if ($model->getCodeModel()) {
            $data->setCode($model->getCodeModel()->getCode());
        }
    }
}
