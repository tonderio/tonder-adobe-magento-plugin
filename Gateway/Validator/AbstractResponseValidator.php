<?php

namespace Tonder\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class AbstractResponseValidator
 */
abstract class AbstractResponseValidator extends AbstractValidator
{

    /**
     * The amount that was authorised for this transaction
     */
    const TOTAL_AMOUNT = "amount";


    /**
     * A unique identifier that represents the transaction in eWAY’s system
     */
    const TRANSACTION_ID = "id";

    /**
     * A code that describes the result of the action performed
     */
    const RESPONSE_MESSAGE = 'message';

    /**
     * The two digit response code returned from the bank
     */
    const RESPONSE_CODE = 'ResponseCode';

    /**
     * This is the payment_id
     */
    const PK = "pk";

    /**
     * This is the order ID for create order
     */
    const ORDER_ID = "id";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateErrors(array $response)
    {
        $responseCode = $response[self::RESPONSE_CODE];
        return ($responseCode !== null && (int)$responseCode === 200) || (int)$responseCode === 201;
    }
    /**
     * @param array $response
     * @param array|number|string $amount
     * @return bool
     */
    protected function validateTotalAmount(array $response, $amount)
    {
        if (isset($response["response"]) && is_array($response["response"])) {
            return (
                isset($response["response"][self::TOTAL_AMOUNT])
                && (float)$response["response"][self::TOTAL_AMOUNT] === (float)$amount
            );
        }

        return (
            isset($response[self::TOTAL_AMOUNT])
            && (float)$response[self::TOTAL_AMOUNT] === (float)$amount
        );
    }
    /**
     * @param array $response
     * @return bool
     */

    protected function validateTransactionId(array $response)
    {
        return isset($response["response"][self::TRANSACTION_ID]);
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
     * @return bool
     */
    protected function validateResponseMessage(array $response)
    {
        return !empty($response[self::RESPONSE_MESSAGE]);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validatePaymentId(array $response){
        return isset($response[self::PK]);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateOrderID(array $response){
        return isset($response[self::ORDER_ID]);
    }
}
