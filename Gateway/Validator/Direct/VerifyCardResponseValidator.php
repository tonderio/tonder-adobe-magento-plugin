<?php
/**
 * Copyright © Tonder JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 11/05/2020
 * Time: 16:12
 */

namespace Tonder\Payment\Gateway\Validator\Direct;


use Tonder\Payment\Gateway\Validator\AbstractResponseValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;

class VerifyCardResponseValidator extends AbstractResponseValidator
{

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTransactionId($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response);

        if (!$this->validateErrors($response) && $this->validateResponseMessage($response)) {
            throw new LocalizedException(__('Your AVS/CVD information is invalid. Please re-check and try again'));
        }
        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
