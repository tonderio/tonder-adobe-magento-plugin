<?php
namespace Tonder\Payment\Gateway\Validator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
/**
 * Class TransactionCaptureValidator
 * @package Tonder\Payment\Gateway\Validator
 */
class TransactionCaptureValidator extends AbstractResponseValidator
{
    /**
     * TransactionCaptureValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ResultInterfaceFactory $resultFactory, ScopeConfigInterface $scopeConfig)
    {
        parent::__construct($resultFactory, $scopeConfig);
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTotalAmount($response, $amount)
            && $this->validateTransactionId($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response);

        if (!$validationResult && $this->validateResponseMessage($response)) {
            throw new LocalizedException(__($response[AbstractResponseValidator::RESPONSE_MESSAGE]));
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * @param array $response
     * @param array|number|string $amount
     * @return bool
     */
    protected function validateTotalAmount(array $response, $amount)
    {
        return isset($response[0]['amount'])
            && (float)$response[0]['amount'] === (float)$amount;
    }
}
