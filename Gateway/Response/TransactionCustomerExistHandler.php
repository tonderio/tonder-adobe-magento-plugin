<?php
namespace Tonder\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\Config;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CustomerExistHandler
 * @package Tonder\Payment\Gateway\Response
 */
class TransactionCustomerExistHandler implements HandlerInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * CardDetailsHandler constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $tonder_id = $response['user_id'] ?? "";
        if ($tonder_id) {
            $payment->setAdditionalInformation(
                'tonder_id',
                $tonder_id
            );
            $auth_token = $response['token'] ?? "";
            $payment->setAdditionalInformation('auth_token',$auth_token);
        }
    }
}
