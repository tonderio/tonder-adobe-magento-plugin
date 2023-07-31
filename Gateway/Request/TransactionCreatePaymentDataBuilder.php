<?php
declare(strict_types=1);

namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CreatePaymentDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionCreatePaymentDataBuilder extends AbstractDataBuilder
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * AbstractDataBuilder constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        EncryptorInterface $encryptor
    )
    {
        $this->config = $config;
        $this->encryptor = $encryptor;
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


        $tokenEncriptor = $methodInstance->getConfigData('token');
        $token = $this->getToken($tokenEncriptor);

        return [
            "business" => $methodInstance->getConfigData('merchant_id'),
            "business_pk" =>$token,
            "client_id" => $tonder_id,
            "date" => $createData,
            "order" => $order_id, //Id that cames from the order creation
            "source" => "Magento",
        ];
    }

    public function getToken($tokenEncriptor)
    {
        return $this->encryptor->decrypt($tokenEncriptor);
    }
}
