<?php
namespace Tonder\Payment\Gateway\Helper;

/**
 * Class SubjectReader
 */
class SubjectReader
{
    /**
     * Reads access code from subject
     *
     * @param array $subject
     * @return string
     */
    public function readAccessCode(array $subject)
    {
        if (empty($subject['access_code'])) {
            throw new \InvalidArgumentException('Access code should be provided.');
        }

        return $subject['access_code'];
    }

    /**
     * Read transaction id from subject
     *
     * @param array $subject
     * @return string
     */
    public function readTransactionId(array $subject)
    {
        if (!isset($subject['request']['transaction_id'])
            || !is_string($subject['request']['transaction_id'])
        ) {
            throw new \InvalidArgumentException('Transaction id does not exist');
        }

        return $subject['request']['transaction_id'];
    }

    /**
     * Reads ticket from subject
     *
     * @param array $subject
     * @return string
     */
    public function readTicket(array $subject)
    {
        if (empty($subject['ticket'])) {
            throw new \InvalidArgumentException('Ticket should be provided.');
        }

        return $subject['ticket'];
    }

    /**
     * Reads hpp_id from subject
     *
     * @param array $subject
     * @return string
     */
    public function readHppId(array $subject)
    {
        if (empty($subject['hpp_id'])) {
            throw new \InvalidArgumentException('Hpp_id should be provided.');
        }

        return $subject['hpp_id'];
    }

    /**
     * Reads payment_url from subject
     *
     * @param array $subject
     * @return string
     */
    public function readPaymentUrl(array $subject)
    {
        if (empty($subject['paymentUrl'])) {
            throw new \InvalidArgumentException('Payment Url should be provided.');
        }

        return $subject['paymentUrl'];
    }
}
