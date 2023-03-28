<?php

namespace Tonder\Payment\Gateway\Request;

use Tonder\Payment\Gateway\Helper\MappingCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class RefundAmountDataBuilder extends AmountDataBuilder
{
    /**
     * RefundAmountDataBuilder constructor.
     * @param MappingCurrency $mappingCurrency
     * @param ScopeConfigInterface $config
     */
    public function __construct(MappingCurrency $mappingCurrency, ScopeConfigInterface $config)
    {
        parent::__construct($mappingCurrency, $config);
    }

    /**
     * @param array $buildSubject
     * @return array|array[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        $paymentData = $buildSubject['payment'];
        $code = $paymentData->getPayment()->getOrder()->getOrderCurrencyCode();
        $currencyData = $this->mappingCurrency->getCurrencyData($code);
        $additionalInformation = $paymentData->getPayment()->getAdditionalInformation();
        $amount = SubjectReader::readAmount($buildSubject);
        if (isset($additionalInformation['mcp_purchase']) && $additionalInformation['mcp_purchase'] == 'Yes') {
            $amountRefund = $paymentData->getPayment()->getCreditMemo()->getGrandTotal();
            if ($currencyData['currency_code_number'] != '124') {
                $amount = $currencyData['rate'] > 1 ? (int)round($amountRefund * 100) : (int)round($amountRefund);
                return [
                    self::REPLACE_KEY => [
                        self::CARDHOLDER_AMOUNT => sprintf($amount),
                        self::CARDHOLDER_CURRENCY_CODE => $currencyData['currency_code_number'],
                        self::MCP_VERSION => '1.0'
                    ]
                ];
            } else {
                $amount =  round($amountRefund, 2);
                return [
                    self::REPLACE_KEY => [
                        self::AMOUNT => sprintf('%.2F', $amount)
                    ]
                ];
            }
        }
        return [
            self::REPLACE_KEY => [
                self::AMOUNT => sprintf('%.2F', $amount)
            ]
        ];
    }
}
