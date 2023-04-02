<?php
namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Tonder\Payment\Observer\DataAssignObserver;

/**
 * Class CardDetailsDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class CardDetailsDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    const SKYFLOW_ID = 'skyflow_id';

    const CARD_NUMBER = 'card_number';

    const CARDHOLDER_NAME = 'cardholder_name';

    const CVV = 'cvv';

    const EXPIRATION_MONTH = 'expiration_month';

    const EXPIRATION_YEAR = 'expiration_year';


    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * CardDetailsDataBuilder constructor.
     *
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $data = $payment->getAdditionalInformation();
        $month = $this->formatMonth($data[OrderPaymentInterface::CC_EXP_MONTH]);
        $year = substr($data[OrderPaymentInterface::CC_EXP_YEAR], 2, 3);
        $cardNumber = $data[OrderPaymentInterface::CC_NUMBER_ENC];
        $cardNumber = $this->encryptor->decrypt($cardNumber);
        $cardHolderName = $this->encryptor->decrypt($data[DataAssignObserver::CC_CARD_HOLDER]);

        return [
            "processor" => [
                "id" => 2,
                "resourcetype" => "StripeBusinessConnection",
                "processor_type" => "CHECKOUT"
            ],
            "card" => [
                self::SKYFLOW_ID => 123,
                self::CARD_NUMBER => "",
                self::CARDHOLDER_NAME => "",
                self::CVV => "",
                self::EXPIRATION_MONTH => $month,
                self::EXPIRATION_YEAR => $year,
            ]
        ];
    }

    /**
     * @param string $month
     * @return null|string
     */
    private function formatMonth($month)
    {
        return !empty($month) ? sprintf('%02d', $month) : null;
    }

    private function skyFlowTokenization($data)
    {

    }
}
