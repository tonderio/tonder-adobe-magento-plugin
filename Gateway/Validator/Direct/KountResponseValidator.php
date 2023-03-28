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

class KountResponseValidator extends AbstractResponseValidator
{
    const KOUNT_TRANSACTION_ID = 'KountTransactionId';
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws LocalizedException
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateKountTransactionId($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response);

        if (!$this->validateErrors($response) && $this->validateResponseMessage($response)) {
            throw new LocalizedException(__($response[self::RESPONSE_MESSAGE]));
        }
        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * @param array $response
     * @return bool
     */
    public function validateKountTransactionId(array $response)
    {
        return isset($response[self::KOUNT_TRANSACTION_ID])
            && $response[self::KOUNT_TRANSACTION_ID] != 'null';
    }
}
