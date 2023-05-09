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
     * @var array
     */
    private $additionalInformationMapping = [
        'transaction_type' => 'response/data/payment_method_types/0',
        'transaction_id' => 'response/data/id',
        'response_code' => 'response/data/status',
        'reference_num' => 'response/data/latest_charge'
    ];
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $responseObj = new DataObject($response);

        $payment->setTransactionId($response['response']['data'][AbstractResponseValidator::TRANSACTION_ID]);
        $payment->setLastTransId($response['response']['data'][AbstractResponseValidator::TRANSACTION_ID]);
        $payment->setIsTransactionClosed(false);

        foreach ($this->additionalInformationMapping as $informationKey => $responseKey) {
            if ($responseObj->getData($responseKey)) {
                $payment->setAdditionalInformation($informationKey, $responseObj->getData($responseKey));
            }
        }
    }
}
