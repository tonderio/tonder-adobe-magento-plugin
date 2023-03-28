<?php
namespace Tonder\Payment\Gateway\Validator\Net;

use Tonder\Payment\Gateway\Validator\AbstractResponseValidator;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;

/**
 * Class UpdateDetailsValidator
 * @package Tonder\Payment\Gateway\Validator\Net
 */
class UpdateDetailsValidatorUS extends AbstractResponseValidator
{
    const AMOUNT = 'amount';
    const PAYMENT_NUMBER = 'txn_num';
    const NET_RESPONSE_CODE = 'response_code';
    const PAYMENT_REFERENCE = 'ref_num';
    const NET_RESPONSE_MESSAGE = 'message';
    const TRANSACTION_TYPE = 'txn_type';
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDO = SubjectReader::readPayment($validationSubject);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $order = $paymentDO->getOrder();
        $errorMessages = [];

        $validationResult = $this->validateAmount($response, $order->getGrandTotalAmount())
            && $this->validatePaymentNumber($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response)
            && $this->validatePaymentReference($response);

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * @param array $response
     * @param float $amount
     * @return bool
     */
    private function validateAmount(array $response, $amount)
    {
        $amount = number_format($amount, 2, '.', '');
        return !empty($response[self::AMOUNT]) && (float)$response[self::AMOUNT] === (float)$amount;
    }

    /**
     * @param array $response
     * @return bool
     */
    private function validatePaymentNumber(array $response)
    {
        return !empty($response[self::PAYMENT_NUMBER]);
    }

    /**
     * @param array $response
     * @return bool
     */
    private function validatePaymentReference(array $response)
    {
        return !empty($response[self::PAYMENT_REFERENCE]);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateResponseCode(array $response)
    {
        return isset($response[self::NET_RESPONSE_CODE])
            && $response[self::NET_RESPONSE_CODE] < 50;
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateResponseMessage(array $response)
    {
        return !empty($response[self::NET_RESPONSE_MESSAGE]);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateTransactionType(array $response)
    {
        return !empty($response[self::TRANSACTION_TYPE]);
    }
}
