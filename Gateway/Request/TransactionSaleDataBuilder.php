<?php
declare(strict_types=1);

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionSaleDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionSaleDataBuilder extends AbstractDataBuilder
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $methodInstance = $paymentDO->getPayment()->getMethodInstance();
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $tonder_id = $payment->getAdditionalInformation("tonder_id");
        $payment_id = $payment->getAdditionalInformation("payment_id");

        return [
            "description" => 'Payment for order #' . $order->getOrderIncrementId(),
            "source"=> "Magento",
            "device_session_id" => session_id(),
            "token_id" => "",
            "order_id" => $order->getOrderIncrementId(),
            "business_id" => $methodInstance->getConfigData('merchant_id'),
            "client_id" => $tonder_id ,
            "payment_id" => $payment_id,//for token the orden api
            "return_url"=>"",
        ];
    }
}
