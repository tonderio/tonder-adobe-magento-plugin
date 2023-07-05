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
     * The transaction type that this transaction was processed under
     * One of: Purchase, MOTO, Recurring
     */
    const TRANSACTION_TYPE = 'TransType';

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
        return $response[self::RESPONSE_CODE] !== 'null' && (int)$response[self::RESPONSE_CODE] === 200;
    }

    /**
     * @param array $response
     * @param array|number|string $amount
     * @return bool
     */
    protected function validateTotalAmount(array $response, $amount)
    {
        return (float)$response["response"][self::TOTAL_AMOUNT] === (float)$amount;
    }
    /**
     * @param array $response
     * @return bool
     */

    protected function validateTransactionId(array $response)
    {
        return isset($response["response"]["charges"]["data"][0][self::TRANSACTION_ID]);
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
