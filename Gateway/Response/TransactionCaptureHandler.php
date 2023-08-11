<?php
declare(strict_types=1);

namespace Tonder\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Model\Order;
use Tonder\Payment\Gateway\Validator\AbstractResponseValidator;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionCaptureHandler
 */
class TransactionCaptureHandler implements HandlerInterface
{
    const TRANSACTION_ID = "id";

    const TRANSACTION_STATUS = "transaction_status";

    const RESPONSE_CODE = 'ResponseCode';

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        try {
            $transctationStatus = $this->transactionStatus($response);
            if ($transctationStatus === 'Success') {
                $transctationId = $this->getTransactionId($response);

                $payment->setTransactionId($transctationId);
                $payment->setLastTransId($transctationId);
                $payment->setIsTransactionClosed(true);
            } else {
                $payment->getOrder()->setState(Order::STATE_PENDING_PAYMENT);
                $payment->getOrder()->setStatus(Order::STATE_PENDING_PAYMENT);
                $payment->setIsTransactionPending(true);
                $payment->setAdditionalInformation('error_messages', 'Error this Capture Tonder');
            }
        } catch (\Exception $e) {
            $payment->setAdditionalInformation('error_messages', $e->getMessage());
        }
    }

    private function getTransactionId(array $response)
    {
        if (isset($response['psp_response']['response']) && is_array($response['psp_response']['response'])) {
            return $response["psp_response"]['response'][self::TRANSACTION_ID];
        } elseif (isset($response["psp_response"]) && is_array($response["psp_response"])) {
            return
                $response["psp_response"][self::TRANSACTION_ID];
        }
        return $response[self::TRANSACTION_ID];
    }

    protected function transactionStatus(array $response)
    {
        if (isset($response[self::TRANSACTION_STATUS])) {
            return $response[self::TRANSACTION_STATUS];
        }
        return null;
    }



}
