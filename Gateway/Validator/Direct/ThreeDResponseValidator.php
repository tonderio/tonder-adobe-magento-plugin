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

class ThreeDResponseValidator extends AbstractResponseValidator
{

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $multi_currency = $this->scopeConfig->getValue('payment/moneris/multi_currency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $amount = SubjectReader::readAmount($validationSubject);
        $paymentData = $validationSubject['payment']->getPayment();
        if ($multi_currency) {
            $amount = round($paymentData->getAmountOrdered(), 2);
        }

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTotalAmount($response, $amount)
            && $this->validateTransactionId($response)
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
}
