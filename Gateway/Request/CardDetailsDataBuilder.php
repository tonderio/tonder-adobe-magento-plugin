<?php
declare(strict_types=1);
namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Tonder\Payment\Helper\SkyFlowProcessor;
use Tonder\Payment\Observer\DataAssignObserver;

/**
 * Class CardDetailsDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class CardDetailsDataBuilder extends AbstractDataBuilder
{
    const CVV = 'cvv';

    const CARD ="card";

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var SkyFlowProcessor
     */
    private $skyFlowProcessor;

    /**
     * CardDetailsDataBuilder constructor.
     *
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor,
        SkyFlowProcessor $skyFlowProcessor
    ) {
        $this->encryptor = $encryptor;
        $this->skyFlowProcessor = $skyFlowProcessor;
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
        $cvv = $this->encryptor->decrypt($data[DataAssignObserver::CC_CID_ENC]);
        $cardHolderName = $this->encryptor->decrypt($data[DataAssignObserver::CC_CARD_HOLDER]);

        $skyFlowData = $this->skyFlowTokenization($payment, [
            'card_number' => $cardNumber,
            'cardholder_name' => $cardHolderName,
            'expiration_month' => $month,
            'expiration_year' => $year,
            'cvv' => $cvv
        ]);
        var_dump( $skyFlowData);
        $skyFlowData['tokens']['skyflow_id'] = $skyFlowData['skyflow_id'];

        return [
            self::CARD => $skyFlowData['tokens']
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

    /**
     * @param $payment
     * @param $creditData
     * @return array|mixed
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    private function skyFlowTokenization($payment, $creditData)
    {
        return $this->skyFlowProcessor->tokenization($payment, $creditData);
    }
}
