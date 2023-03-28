<?php
namespace Tonder\Payment\Gateway\Command;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class InitializeCommand
 */
class InitializeCommand implements CommandInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        $state = SubjectReader::readStateObject($commandSubject);
        $paymentObject = SubjectReader::readPayment($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentObject->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setAmountAuthorized($payment->getOrder()->getTotalDue());
        $payment->setBaseAmountAuthorized($payment->getOrder()->getBaseTotalDue());
        $payment->getOrder()->setCanSendNewEmailFlag(false);

        $state->setData(OrderInterface::STATE, Order::STATE_PENDING_PAYMENT);
        $state->setData(OrderInterface::STATUS, Order::STATE_PENDING_PAYMENT);
        $state->setData('is_notified', false);
    }
}
