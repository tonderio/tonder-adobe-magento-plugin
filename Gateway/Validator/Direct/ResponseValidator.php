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
        $amount = SubjectReader::readAmount($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateTotalAmount($response, $amount)
            && $this->validateTransactionId($response);

        if (!$validationResult) {
            $errorMessages = [__('Error in Capture Order. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
