<?php

namespace Tonder\Payment\Gateway\Response;

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
        'transaction_type' => AbstractResponseValidator::TRANSACTION_TYPE,
        'transaction_id' => AbstractResponseValidator::TRANSACTION_ID,
        'response_code' => AbstractResponseValidator::RESPONSE_CODE,
        'reference_num' => AbstractResponseValidator::REFERENCE_NUM,
        'auth_code' => AbstractResponseValidator::AUTH_CODE,
        'cc_type' => 'CardType'
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

        $payment->setTransactionId($response[AbstractResponseValidator::TRANSACTION_ID]);
        $payment->setLastTransId($response[AbstractResponseValidator::TRANSACTION_ID]);
        $payment->setIsTransactionClosed(false);

        foreach ($this->additionalInformationMapping as $informationKey => $responseKey) {
            if (isset($response[$responseKey])) {
                $payment->setAdditionalInformation($informationKey, $response[$responseKey]);
            }
        }
        $multiCurrency = $this->scopeConfig->getValue('payment/moneris/multi_currency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $currencyCode = $paymentDO->getPayment()->getOrder()->getOrderCurrencyCode();
        $enableMCPPurchase = isset($response['MCPRate']) || ($multiCurrency && $currencyCode == 'CAD') ? 'Yes' : 'No';
        $payment->setAdditionalInformation('mcp_purchase', $enableMCPPurchase);
    }
}
