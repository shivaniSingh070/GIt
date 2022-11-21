<?php
declare(strict_types=1);

namespace Amasty\GiftCardSmsNotifications\Block\Adminhtml\Buttons\Account;

use Amasty\GiftCardSmsNotifications\Model\SmsConfigProvider;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndSendSmsButton implements ButtonProviderInterface
{
    const ADMIN_RESOURCE = 'Amasty_GiftCardSmsNotifications::send_sms_notifications';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var SmsConfigProvider
     */
    private $smsConfigProvider;

    public function __construct(
        AuthorizationInterface $authorization,
        SmsConfigProvider $smsConfigProvider
    ) {
        $this->authorization = $authorization;
        $this->smsConfigProvider = $smsConfigProvider;
    }

    public function getButtonData(): array
    {
        if ($this->authorization->isAllowed(self::ADMIN_RESOURCE) && $this->smsConfigProvider->isSmsNotify()) {
            return [
                'label' => __('Save & Send Sms'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'amgcard_account_formedit.areas',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        ['send_sms' => true, 'back' => 'edit'],
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
                'on_click' => '',
                'sort_order' => 45
            ];
        }

        return [];
    }
}
