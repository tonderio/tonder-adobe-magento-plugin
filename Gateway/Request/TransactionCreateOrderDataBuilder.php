<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionCreateOrderDataBuilder
 * @package Tonder\Payment\Gateway\Request
 */
class TransactionCreateOrderDataBuilder extends AbstractDataBuilder
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
     * @param ConfigInterface $config
     * @param EncryptorInterface $encryptor
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
        $order = $paymentDO->getOrder();
        $methodInstance = $paymentDO->getPayment()->getMethodInstance();
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $tokenEncriptor = $methodInstance->getConfigData('token');
        $token = $this->getToken($tokenEncriptor);
        $auth_token = $payment->getAdditionalInformation('auth_token');

        if(!empty($auth_token) && !empty($token) ){
            return [
                "business" =>  $token, // API key provided by Tonder
                "client" => $auth_token, // Token present on the Client Responses
                "payment_method" => 'Tonder', // Payment
                "reference" => $order->getOrderIncrementId(),
                "is_oneclick" => true
            ];
        }
        return [];
    }

    public function getToken($tokenEncriptor)
    {
        return $this->encryptor->decrypt($tokenEncriptor);
    }
}
