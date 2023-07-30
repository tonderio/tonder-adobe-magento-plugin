<?php

namespace Tonder\Payment\Gateway\Response;

use Magento\Framework\DataObject;
use Tonder\Payment\Gateway\Validator\AbstractResponseValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class PaymentDetailsHandler
 */
class PaymentDetailsHandler implements HandlerInterface
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
        $transctationId = $response["response"][AbstractResponseValidator::TRANSACTION_ID] ?? "";

        $payment->setTransactionId( $transctationId);
        $payment->setLastTransId( $transctationId);
        $payment->setIsTransactionClosed(false);
    }
}
