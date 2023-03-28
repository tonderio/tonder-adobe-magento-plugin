<?php
namespace Tonder\Payment\Gateway\Helper;

/**
 * Class TransactionReader
 */
class ResponseReader
{
    const RESPONSE = 'response';

    /**
     * @param array $transactionData
     * @return array
     */
    public function readResponse(array $transactionData)
    {
        if (empty($transactionData[self::RESPONSE])) {
            throw new \InvalidArgumentException('Could not read response');
        }

        return $transactionData[self::RESPONSE];
    }
}
