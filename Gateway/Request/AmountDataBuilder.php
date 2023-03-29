<?php

namespace Tonder\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Tonder\Payment\Gateway\Helper\MappingCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AmountDataBuilder
 *
 * @package Tonder\Payment\Gateway\Request
 */
class AmountDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * @var MappingCurrency
     */
    protected $mappingCurrency;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * AmountDataBuilder constructor.
     * @param MappingCurrency $mappingCurrency
     * @param ScopeConfigInterface $config
     */
    public function __construct(MappingCurrency $mappingCurrency, ScopeConfigInterface $config)
    {
        $this->mappingCurrency = $mappingCurrency;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $multiCurrency = $this->config->getValue('payment/tonder/multi_currency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $paymentData = $buildSubject['payment'];
        $code = $paymentData->getPayment()->getOrder()->getOrderCurrencyCode();
        if ($multiCurrency) {
            $currencyData = $this->mappingCurrency->getCurrencyData($code);
            if ($currencyData['currency_code_number'] != '124') {
                $amount = $currencyData['rate'] > 1 ? (int)round($paymentData->getPayment()->getAmountOrdered() * 100) : (int)round($paymentData->getPayment()->getAmountOrdered());
                return [
                    self::REPLACE_KEY => [
                        self::CARDHOLDER_AMOUNT => sprintf($amount),
                        self::CARDHOLDER_CURRENCY_CODE => $currencyData['currency_code_number'],
                        self::MCP_VERSION => '1.0'
                    ]
                ];
            } else {
                $amount =  round($paymentData->getPayment()->getAmountOrdered(), 2);
                return [
                    self::REPLACE_KEY => [
                        self::AMOUNT => sprintf('%.2F', $amount)
                    ]
                ];
            }
        }
        return [
            self::REPLACE_KEY => [
                self::AMOUNT => sprintf('%.2F', SubjectReader::readAmount($buildSubject))
            ]
        ];
    }
}
