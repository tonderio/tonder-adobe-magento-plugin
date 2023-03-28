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
    const TOTAL_AMOUNT = 'TransAmount';

    /**
     * The transaction type that this transaction was processed under
     * One of: Purchase, MOTO, Recurring
     */
    const TRANSACTION_TYPE = 'TransType';

    /**
     * A unique identifier that represents the transaction in eWAY’s system
     */
    const TRANSACTION_ID = 'TransID';

    /**
     * A code that describes the result of the action performed
     */
    const RESPONSE_MESSAGE = 'Message';

    /**
     * The two digit response code returned from the bank
     */
    const RESPONSE_CODE = 'ResponseCode';

    /**
     * Value of response code
     */
    const RESPONSE_CODE_ACCEPT = '00';

    /**
     * Reference Number
     */
    const REFERENCE_NUM = 'ReferenceNum';

    const AUTH_CODE = 'AuthCode';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * AbstractResponseValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ResultInterfaceFactory $resultFactory, ScopeConfigInterface $scopeConfig)
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
        return $response[self::RESPONSE_CODE] != 'null' && (int)$response[self::RESPONSE_CODE] < 50;
    }

    /**
     * @param array $response
     * @param array|number|string $amount
     * @return bool
     */
    protected function validateTotalAmount(array $response, $amount)
    {
        $multiCurrency = $this->scopeConfig->getValue('payment/moneris/multi_currency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($multiCurrency && isset($response['MCPRate'])) {
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

    /**
     * @param array $response
     * @return bool
     */
    protected function validateTransactionType(array $response)
    {
        return isset($response[self::TRANSACTION_TYPE])
            && ($response[self::TRANSACTION_TYPE] == '00'
                || $response[self::TRANSACTION_TYPE] == '01'
                || $response[self::TRANSACTION_TYPE] == '02'
                || $response[self::TRANSACTION_TYPE] == '04'
                || $response[self::TRANSACTION_TYPE] == '11');
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateTransactionId(array $response)
    {
        return isset($response[self::TRANSACTION_ID])
            && $response[self::TRANSACTION_ID] != 'null';
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
}
