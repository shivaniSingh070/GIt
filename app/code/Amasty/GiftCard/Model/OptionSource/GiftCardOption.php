<?php
declare(strict_types=1);

namespace Amasty\GiftCard\Model\OptionSource;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Magento\Framework\Data\OptionSourceInterface;

class GiftCardOption implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['label' => $label, 'value' => $value];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getAllDisplayOptions(): array
    {
        return [
            GiftCardOptionInterface::GIFTCARD_AMOUNT   => __('Card Value'),
            GiftCardOptionInterface::SENDER_NAME       => __('Sender Name'),
            GiftCardOptionInterface::RECIPIENT_PHONE   => __('Recipient Phone'),
            GiftCardOptionInterface::RECIPIENT_NAME    => __('Recipient Name'),
            GiftCardOptionInterface::RECIPIENT_EMAIL   => __('Recipient Email'),
            GiftCardOptionInterface::DELIVERY_DATE     => __('Delivery Date'),
            GiftCardOptionInterface::DELIVERY_TIMEZONE => __('Delivery Timezone'),
            GiftCardOptionInterface::MESSAGE           => __('Message')
        ];
    }

    /**
     * @return array
     */
    public function getOrderOptionsKeys(): array
    {
        return [
            GiftCardOptionInterface::GIFTCARD_AMOUNT,
            GiftCardOptionInterface::IMAGE,
            GiftCardOptionInterface::GIFTCARD_TYPE,
            GiftCardOptionInterface::SENDER_NAME,
            GiftCardOptionInterface::SENDER_EMAIL,
            GiftCardOptionInterface::RECIPIENT_NAME,
            GiftCardOptionInterface::RECIPIENT_EMAIL,
            GiftCardOptionInterface::RECIPIENT_PHONE,
            GiftCardOptionInterface::DELIVERY_DATE,
            GiftCardOptionInterface::DELIVERY_TIMEZONE,
            GiftCardOptionInterface::MESSAGE
        ];
    }
}
