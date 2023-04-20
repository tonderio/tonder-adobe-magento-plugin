<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Tonder\Payment\Helper\SkyFlowProcessor;
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

    private $skyFlowProcessor;

    /**
     * CardDetailsDataBuilder constructor.
     *
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor,
        SkyFlowProcessor   $skyFlowProcessor
    ) {
        $this->encryptor        = $encryptor;
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
        $data           = $payment->getAdditionalInformation();
        $month          = $this->formatMonth($data[OrderPaymentInterface::CC_EXP_MONTH]);
        $year           = substr($data[OrderPaymentInterface::CC_EXP_YEAR], 2, 3);
        $cardNumber     = $data[OrderPaymentInterface::CC_NUMBER_ENC];
        $cardNumber     = $this->encryptor->decrypt($cardNumber);
        $cvv            = $this->encryptor->decrypt($data[DataAssignObserver::CC_CID_ENC]);
        $cardHolderName = $this->encryptor->decrypt($data[DataAssignObserver::CC_CARD_HOLDER]);

        $skyFlowData                               = $this->skyFlowTokenization($payment, [
            'card_number' => $cardNumber,
            'cardholder_name' => $cardHolderName,
            'expiry_month' => $month,
            'expiry_year' => $year,
            'cvv' => $cvv
        ]);
        $skyFlowData['tokens']['skyflow_id']       = $skyFlowData['skyflow_id'];
        $skyFlowData['tokens']['expiration_month'] = $skyFlowData['tokens']['expiry_month'];
        $skyFlowData['tokens']['expiration_year']  = $skyFlowData['tokens']['expiry_year'];
        unset($skyFlowData['tokens']['expiry_month']);
        unset($skyFlowData['tokens']['expiry_year']);
//        $skyFlowData['tokens'] = [
//            "skyflow_id" => "09541643-4f26-455e-a462-734835a5ebad",
//            "card_number" => "2149-8690-2272-4430",
//            "cardholder_name" => "4c6847c4-6a2d-4d59-ac48-58aaf624d0ff",
//            "cvv" => "34aab0a2-01db-47b3-837f-6c83668ed46e",
//            "expiration_month" => "c4003819-fa74-4671-9a51-9157b19ae35a",
//            "expiration_year" => "9a503795-5ad8-4fde-8193-923fb5a0ecc2"
//        ];
        return [
//            "processor" => [
//                "id" => 2,
//                "resourcetype" => "StripeBusinessConnection",
//                "processor_type" => "CHECKOUT"
//            ],
            "card" => $skyFlowData['tokens']
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

    private function skyFlowTokenization($payment, $creditData)
    {
        return $this->skyFlowProcessor->tokenization($payment, $creditData);
    }
}
