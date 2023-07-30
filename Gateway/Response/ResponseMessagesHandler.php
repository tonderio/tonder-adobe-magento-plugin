<?php
namespace Tonder\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class ResponseMessagesHandler
 */
class ResponseMessagesHandler implements HandlerInterface
{

    const RESPONSE_CODE = 'ResponseCode';
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $messages = $response['message'];
        $state = $this->getState($response);

        if ($state) {
            $payment->setAdditionalInformation(
                'approve_messages',
                $messages
            );
        } else {
            $payment->setIsTransactionPending(false);
            $payment->setIsFraudDetected(true);
            $payment->setAdditionalInformation('error_messages', $messages);
        }
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function getState(array $response)
    {
        $responseCode = $response[self::RESPONSE_CODE];
        return ($responseCode !== null && (int)$responseCode === 200) || (int)$responseCode === 201;
    }
}
