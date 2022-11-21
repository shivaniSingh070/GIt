<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Command\Redeem;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Magento\Framework\Validator\AbstractValidator;

class Validator extends AbstractValidator
{
    /**
     * @var AbstractValidator[]
     */
    private $commonValidators;

    /**
     * @var AbstractValidator[]
     */
    private $byKeyValidators;

    /**
     * @var string
     */
    private $validatorKey;

    /**
     * @param AbstractValidator[] $commonValidators
     */
    public function __construct(
        array $commonValidators = [],
        array $byKeyValidators = [],
        string $validatorKey = null
    ) {
        $this->commonValidators = $commonValidators;
        $this->byKeyValidators = $byKeyValidators;
        $this->validatorKey = $validatorKey;
    }

    /**
     * @param GiftCardAccountInterface $value
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();
        $validatorsByKey = isset($this->byKeyValidators[$this->validatorKey])
            ? $this->byKeyValidators[$this->validatorKey]
            : [];
        $validators = array_merge($this->commonValidators, $validatorsByKey);
        foreach ($validators as $validator) {
            if (!$validator->isValid($value)) {
                $this->_addMessages($validator->getMessages());
            }
        }

        return empty($this->getMessages());
    }
}
