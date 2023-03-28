<?php
namespace Tonder\Payment\Gateway\Response\Net;

use Tonder\Payment\Gateway\Validator\Net\UpdateDetailsValidatorUS;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class UpdateDetailsHandler
 * @package Tonder\Payment\Gateway\Response\Net
 */
class UpdateDetailsHandlerUS implements HandlerInterface
{
    /**
     * @var array
     */
    private $additionalInformationMapping = [
        'transaction_id' => UpdateDetailsValidatorUS::PAYMENT_NUMBER,
        'transaction_type' => UpdateDetailsValidatorUS::TRANSACTION_TYPE,
        'reference_number' => UpdateDetailsValidatorUS::PAYMENT_REFERENCE
    ];

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $payment->setTransactionId($response[UpdateDetailsValidatorUS::PAYMENT_NUMBER]);
        $payment->setLastTransId($response[UpdateDetailsValidatorUS::PAYMENT_NUMBER]);
        $payment->setIsTransactionClosed(false);
        $payment->setAdditionalInformation('card_number', $response['card_num']);
        $payment->setAdditionalInformation('cc_type', $this->getCreditCard($response));
        $payment->setAdditionalInformation('card_expiry_date', $this->getExpiryDate($response));
        $payment->setAdditionalInformation('approve_messages', $response['message']);
        foreach ($this->additionalInformationMapping as $informationKey => $responseKey) {
            if (isset($response[$responseKey])) {
                $payment->setAdditionalInformation($informationKey, $response[$responseKey]);
            }
        }
    }

    /**
     * @param $response
     * @return string
     */
    public function getCreditCard($response)
    {
        switch ($response['card_type']) {
            case 'M':
                return 'Mastercard';
            case 'V':
                return 'Visa';
            case 'AX':
                return 'American Express';
            case 'DC':
                return 'Diners Card';
            case 'NO':
                return 'Novus/Discover';
            case 'SE':
                return 'Sears';
            default:
                return 'N/A';
        }
    }

    /**
     * @param $response
     * @return string
     */
    public function getExpiryDate($response)
    {
        $year = $response['exp_year'];
        $month = $response['exp_month'];

        $expiry_date = sprintf('%s/%s', $month, $year);
        return $expiry_date;
    }
}
