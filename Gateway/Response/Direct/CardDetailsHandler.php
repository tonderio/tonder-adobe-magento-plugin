<?php
namespace Tonder\Payment\Gateway\Response\Direct;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\Config;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CardDetailsHandler
 * @package Tonder\Payment\Gateway\Response\Direct
 */
class CardDetailsHandler implements HandlerInterface
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

        $cardType =  $payment->getAdditionalInformation('cc_type');
        if ($cardType) {
            $payment->setAdditionalInformation(
                'cc_type',
                $cardType
            );
        } else {
            $payment->setAdditionalInformation(
                'cc_type',
                'N/A'
            );
        }

        $maskCcNumber = 'XXXX-' .
            substr($payment->decrypt(
                $payment->getAdditionalInformation(OrderPaymentInterface::CC_NUMBER_ENC)
            ), -4);

        $payment->setAdditionalInformation('card_number', $maskCcNumber);

        $payment->setAdditionalInformation(
            'card_expiry_date',
            sprintf(
                '%s/%s',
                $payment->getAdditionalInformation(OrderPaymentInterface::CC_EXP_MONTH),
                $payment->getAdditionalInformation(OrderPaymentInterface::CC_EXP_YEAR)
            )
        );

        $payment->unsAdditionalInformation(OrderPaymentInterface::CC_NUMBER_ENC);
        $payment->unsAdditionalInformation('cc_sid_enc');
    }
}
