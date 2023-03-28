<?php
namespace Tonder\Payment\Gateway\Response;

use Tonder\Payment\Gateway\Validator\AbstractResponseValidator;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionCVoidHandler
 *
 * @package Tonder\Payment\Gateway\Response
 */
class TransactionVoidHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();
        $orderPayment->setTransactionId($response[AbstractResponseValidator::TRANSACTION_ID]);

        $orderPayment->setIsTransactionClosed(true);
        $orderPayment->setShouldCloseParentTransaction(true);
    }
}
