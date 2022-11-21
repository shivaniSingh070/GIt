<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Command\Redeem\Validator;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Validator\AbstractValidator;

class Common extends AbstractValidator
{
    /**
     * @param GiftCardAccountInterface $value
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    public function isValid($value): bool
    {
        $gcCode = $value->getCodeModel()->getCode();

        $errors = [];
        if ($value->getStatus() == AccountStatus::STATUS_REDEEMED) {
            $errors[] = __('Gift Card "%1" has already been redeemed for store credit.', $gcCode);
        }

        if (!in_array($value->getStatus(), [AccountStatus::STATUS_REDEEMED, AccountStatus::STATUS_ACTIVE])) {
            $errors[] = __('Gift Card "%1" cannot be redeemed for store credit.', $gcCode);
        }
        $this->_addMessages($errors);

        return empty($this->getMessages());
    }
}
