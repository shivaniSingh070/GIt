<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class LoginAttemptStatus implements OptionSourceInterface
{
    const FAILED = 0;
    const SUCCESS = 1;
    const LOGOUT = 2;

    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['label' => $label, 'value' => $value];
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            self::FAILED => __('Failed'),
            self::SUCCESS => __('Success'),
            self::LOGOUT => __('Logout')
        ];
    }
}
