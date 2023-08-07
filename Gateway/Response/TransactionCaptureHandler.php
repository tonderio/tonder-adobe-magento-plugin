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
            $transctationId = $response["response"][AbstractResponseValidator::TRANSACTION_ID] ?? "";
            if ($transctationId) {
                $payment->setTransactionId($transctationId);
                $payment->setLastTransId($transctationId);
                $payment->setIsTransactionClosed(true);
            } else {
                $payment->setTransactionId($transctationId);
                $payment->getOrder()->setState(Order::STATE_PENDING_PAYMENT);
                $payment->getOrder()->setStatus(Order::STATE_PENDING_PAYMENT);
                $payment->setIsTransactionPending(true);
                $payment->setAdditionalInformation('error_messages', 'Error this Capture Tonder');
            }
        } catch (\Exception $e) {
            $payment->setAdditionalInformation('error_messages', $e->getMessage());
        }
    }


}
