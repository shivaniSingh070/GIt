<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderImportExport\Model\Data\Validator;

use Ulmod\OrderImportExport\Model\Data\ValidatorInterface;
use Ulmod\OrderImportExport\Model\Data\Validator\ResultFactory;
use Ulmod\OrderImportExport\Model\Data\Validator\ResultInterface;

class RequiredFields implements ValidatorInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var array
     */
    private $requiredFields;

    /**
     * @param array $requiredFields
     */
    public function __construct(
        ResultFactory $resultFactory,
        array $requiredFields
    ) {
        $this->resultFactory  = $resultFactory;
        $this->requiredFields = $requiredFields;
    }

    /**
     * @param array $data
     * @return ResultInterface
     */
    public function validate(array $data)
    {
        $message             = null;
        $choiceMessages      = [];
        $missingFields       = [];
        $missingChoiceFields = [];

        foreach ($this->requiredFields as $field) {
            if (is_array($field)) {
                $requiredCount = key($field);
                $choices       = current($field);

                $existingFields    = [];
                $notExistingFields = [];
                foreach ($choices as $choice) {
                    if (array_key_exists($choice, $data)) {
                        $existingFields[] = $choice;
                    } else {
                        $notExistingFields[] = $choice;
                    }
                }

                if (count($existingFields) < $requiredCount) {
                    $choiceMessage = sprintf(
                        '%d of the following fields are required: %s.',
                        $requiredCount,
                        implode(', ', $choices)
                    );

                    if ($existingFields) {
                        $choiceMessage .= sprintf(
                            ' However, only %s %s present.',
                            implode(', ', $existingFields),
                            count($existingFields) === 1 ? 'is' : 'are'
                        );
                    }

                    $choiceMessages[] = $choiceMessage;
                    $missingChoiceFields[] = [
                        'required' => $requiredCount,
                        'choices'  => $choices,
                        'present'  => $existingFields,
                        'missing'  => $notExistingFields
                    ];
                }
            } elseif (!array_key_exists($field, $data)) {
                $missingFields[] = $field;
            }
        }

        if ($choiceMessages) {
            array_unshift($choiceMessages, $message);
            $choiceMessages = array_filter($choiceMessages, 'strlen');
            $message = implode(PHP_EOL, $choiceMessages);
        }
    
        if ($missingFields) {
            $message = sprintf(
                'The following required fields are missing: %s',
                implode(', ', $missingFields)
            );
        }

        $validateResult = $this->resultFactory->create(
            [
                'isValid'     => empty($missingFields) && empty($missingChoiceFields),
                'message'     => $message,
                'invalidData' => array_merge($missingFields, $missingChoiceFields)
            ]
        );

        return $validateResult;
    }
}
