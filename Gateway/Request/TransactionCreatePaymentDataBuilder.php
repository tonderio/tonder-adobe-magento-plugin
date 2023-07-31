<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionCreatePaymentDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionCreatePaymentDataBuilder extends AbstractDataBuilder
{
    /**
     * @var ConfigInterface
     */
    private $config;


    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    )
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $methodInstance = $paymentDO->getPayment()->getMethodInstance();
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $tonder_id = $payment->getAdditionalInformation("tonder_id");
        $order_id = $payment->getAdditionalInformation("order_id");
        $createData = $payment->getAdditionalInformation("created");
        $token = $payment->getAdditionalInformation("business_token");


        return [
            "business" => $methodInstance->getConfigData('merchant_id'),
            "business_pk" =>$token,
            "client_id" => $tonder_id,
            "date" => $createData,
            "order" => $order_id, //Id that cames from the order creation
            "source" => "Magento",
        ];
    }

}
