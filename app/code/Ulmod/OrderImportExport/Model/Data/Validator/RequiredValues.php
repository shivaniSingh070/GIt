<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Data\Validator;

use Ulmod\OrderImportExport\Model\Data\ValidatorInterface;
use Ulmod\OrderImportExport\Model\Data\Validator\ResultFactory;

class RequiredValues implements ValidatorInterface
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
     * @param array  $data
     * @param string $field
     * @return bool
     */
    private function test(array $data, $field)
    {
        $testResult = true;
        if (!isset($data[$field])) {
            $testResult = false;
        } elseif ($data[$field] === null) {
            $testResult = false;
        } elseif (is_string($data[$field])
            && trim($data[$field]) === ''
        ) {
            $testResult = false;
        }

        return $testResult;
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
                $choices = current($field);

                $existingFields = [];
                $notExistingFields = [];
                foreach ($choices as $choice) {
                    if (!$this->test($data, $choice)) {
                        $notExistingFields[] = $choice;
                    } else {
                        $existingFields[] = $choice;
                    }
                }

                $existingFieldsCount = count($existingFields);
                if ($existingFieldsCount < $requiredCount) {
                    $choiceMessage = sprintf(
                        '%d of the following fields are required and have a value: %s.',
                        $requiredCount,
                        implode(', ', $choices)
                    );

                    if ($existingFieldsCount === 1) {
                        $choiceMessage .= sprintf(
                            ' However, only %s has a value.',
                            implode(', ', $existingFields)
                        );
                    } elseif ($existingFieldsCount > 1) {
                        $choiceMessage .= sprintf(
                            ' However, only %s has values.',
                            implode(', ', $existingFields)
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
            } elseif (!$this->test($data, $field)) {
                $missingFields[] = $field;
            }
        }

        if ($missingFields) {
            $message = sprintf(
                'The following required fields are missing or do not have a value: %s',
                implode(', ', $missingFields)
            );
        }

        if ($choiceMessages) {
            array_unshift($choiceMessages, $message);
            $choiceMessages = array_filter(
                $choiceMessages,
                'strlen'
            );
            $message = implode(
                PHP_EOL,
                $choiceMessages
            );
        }

        $result = $this->resultFactory->create(
            [
                'isValid'     => empty($missingFields) && empty($missingChoiceFields),
                'message'     => $message,
                'invalidData' => array_merge($missingFields, $missingChoiceFields)
            ]
        );

        return $result;
    }
}
