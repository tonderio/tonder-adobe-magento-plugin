<?php

namespace Tonder\Payment\Gateway\Validator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RefundValidator
 *
 * @package Tonder\Payment\Gateway\Validator
 */
class RefundValidator extends TransactionCaptureValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);
        $paymentData = $validationSubject['payment']->getPayment();
        $paymentAdditionalInformation = $paymentData->getAdditionalInformation();
        if (isset($paymentAdditionalInformation['mcp_purchase']) && $paymentAdditionalInformation['mcp_purchase'] == 'Yes') {
            $amount = round($paymentData->getCreditMemo()->getGrandTotal(), 2);
        }
        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTotalAmount($response, $amount)
            && $this->validateTransactionId($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response);

        if (!$validationResult && $this->validateResponseMessage($response)) {
            throw new LocalizedException(__($response[self::RESPONSE_MESSAGE]));
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
