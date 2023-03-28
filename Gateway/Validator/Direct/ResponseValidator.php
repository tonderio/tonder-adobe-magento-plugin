<?php

namespace Tonder\Payment\Gateway\Validator\Direct;

use Tonder\Payment\Gateway\Validator\AbstractResponseValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class ResponseValidator
 * @package Tonder\Payment\Gateway\Validator\Direct
 */
class ResponseValidator extends AbstractResponseValidator
{
    /**
     * ResponseValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ResultInterfaceFactory $resultFactory, ScopeConfigInterface $scopeConfig)
    {
        parent::__construct($resultFactory, $scopeConfig);
    }

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
            && $this->validateTransactionType($response)
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
