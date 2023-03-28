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
        $paymentData = $validationSubject['payment']->getPayment();
        $paymentAdditionalInformation = $paymentData->getAdditionalInformation();
        if (isset($paymentAdditionalInformation['mcp_purchase']) && $paymentAdditionalInformation['mcp_purchase'] == 'Yes') {
            $amount = round($paymentData->getAmountOrdered(), 2);
        }

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
        if (isset($response['MCPRate'])) {
            $rate = (float)$response['MCPRate'];
            if ($rate > 1) {
                return isset($response[self::TOTAL_AMOUNT])
                    && abs((float)$response[self::TOTAL_AMOUNT] / $rate - $amount) < 1;
            } else {
                return isset($response[self::TOTAL_AMOUNT])
                    && abs((float)$response[self::TOTAL_AMOUNT] - $amount * $rate) < 1;
            }
        }
        return isset($response[self::TOTAL_AMOUNT])
            && (float)$response[self::TOTAL_AMOUNT] === (float)$amount;
    }
}
