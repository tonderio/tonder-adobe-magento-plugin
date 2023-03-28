<?php
namespace Tonder\Payment\Gateway\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class VoidValidator
 * @package Tonder\Payment\Gateway\Validator
 */
class GetKeyValidator extends AbstractResponseValidator
{
    const RES_SUCCESS = 'ResSuccess';
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response)
            && $this->validateResult($response);

        if (!$validationResult && $this->validateResponseMessage($response)) {
            throw new LocalizedException(__($response[self::RESPONSE_MESSAGE]));
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateResponseCode(array $response)
    {
        return isset($response[self::RESPONSE_CODE]);
    }

    /**
     * @param array $response
     * @return false|mixed
     */
    protected function validateResult(array $response)
    {
        return isset($response[self::RES_SUCCESS]) ? $response[self::RES_SUCCESS] : false;
    }
}
