<?php
namespace Tonder\Payment\Gateway\Response\Net;

use Tonder\Payment\Gateway\Validator\Net\UpdateDetailsValidator;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class UpdateDetailsHandler
 * @package Tonder\Payment\Gateway\Response\Net
 */
class UpdateDetailsHandler implements HandlerInterface
{
    /**
     * @var array
     */
    private $additionalInformationMapping = [
        'transaction_id' => UpdateDetailsValidator::PAYMENT_NUMBER,
        'response_code' => UpdateDetailsValidator::NET_RESPONSE_CODE,
        'transaction_type' => UpdateDetailsValidator::TRANSACTION_TYPE
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
        $payment->setTransactionId($response[UpdateDetailsValidator::PAYMENT_NUMBER]);
        $payment->setLastTransId($response[UpdateDetailsValidator::PAYMENT_NUMBER]);
        $payment->setIsTransactionClosed(false);
        $payment->setAdditionalInformation('approve_code', $response['bank_approval_code']);
        $payment->setAdditionalInformation('card_number', $response['f4l4']);
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
        switch ($response['card']) {
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
        $year = '20' . substr($response['expiry_date'], 0, 2);
        $month = substr($response['expiry_date'], -2);

        $expiry_date = sprintf('%s/%s', $month, $year);
        return $expiry_date;
    }
}
