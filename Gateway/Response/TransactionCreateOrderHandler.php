<?php
namespace Tonder\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionCreateOrderHandler
 * @package Tonder\Payment\Gateway\Response
 */
class TransactionCreateOrderHandler implements HandlerInterface
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

        $order_id = $response['id'] ?? "";
        if ($order_id) {
            $payment->setAdditionalInformation('order_id', $order_id);
            $dateCreated = $response['created'] ?? "";
            $payment->setAdditionalInformation("created",$dateCreated);
        } else {
            $payment->getOrder()->setState( Order::STATE_PENDING_PAYMENT);
            $payment->getOrder()->setStatus( Order::STATE_PENDING_PAYMENT);;
            $payment->setAdditionalInformation('error_messages', 'Error this order Tonder');
        }
    }
}
