<?php

namespace Tonder\Payment\Gateway\Validator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class TransactionCreateOrderValidator
 * @package Tonder\Payment\Gateway\Validator\Direct
 */
class TransactionCreateOrderValidator extends AbstractResponseValidator
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

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateResponseCode($response)
            && $this->validateOrderID($response)
            && $this->validateResponseMessage($response);


        if (!$this->validateErrors($response) && $this->validateResponseMessage($response)) {
            throw new LocalizedException(__($response[self::RESPONSE_MESSAGE]));
        }

        if (!$validationResult) {
            $errorMessages = [__('Error in Create Order. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
