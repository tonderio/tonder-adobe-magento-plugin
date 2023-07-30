<?php

namespace Tonder\Payment\Gateway\Response;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Tonder\Payment\Gateway\Validator\AbstractResponseValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionCreatePaymentHandler
 */
class TransactionCreatePaymentHandler implements HandlerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * PaymentDetailsHandler constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
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

        $payment_id = $response[AbstractResponseValidator::PK];
        if($payment_id){
            $payment->setAdditionalInformation('payment_id', $payment_id);
            $payment->setIsTransactionClosed(false);
        }else {
            $payment->getOrder()->setState(OrderInterface::STATE, Order::STATE_PENDING_PAYMENT);
            $payment->getOrder()->setStatus(OrderInterface::STATUS, Order::STATE_PENDING_PAYMENT);
            $payment->setIsTransactionPending(true);
            $payment->setAdditionalInformation('error_messages', 'Erro this Payment Tonder');
        }
    }
}
