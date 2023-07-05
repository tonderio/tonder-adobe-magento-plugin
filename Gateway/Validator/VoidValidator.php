<?php
namespace Tonder\Payment\Gateway\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class VoidValidator
 * @package Tonder\Payment\Gateway\Validator
 */
class VoidValidator extends AbstractResponseValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTransactionId($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response);

        if (!$validationResult && $this->validateResponseMessage($response)) {
            throw new LocalizedException(__($response[AbstractResponseValidator::RESPONSE_MESSAGE]));
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
