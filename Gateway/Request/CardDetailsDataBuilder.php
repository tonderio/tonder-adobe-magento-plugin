<?php
namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class CardDetailsDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class CardDetailsDataBuilder extends AbstractDataBuilder implements BuilderInterface
{

    /**
     * The card number that is to be processed for this transaction.
     * (Not required when processing using an existing CustomerTokenID with TokenPayment method).
     * This should be the encrypted value if using Client Side Encryption.
     */
    const PAN = 'pan';

    /**
     * The month that the card expires.
     * (Not required when processing using an existing Customer TokenID with TokenPayment method)
     */
    const EXPIRY_DATE = 'expdate';

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

        return [
            self::REPLACE_KEY => [

                self::PAN => $this->encryptor->decrypt($cardNumber),
                self::EXPIRY_DATE => $year . $month
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
}
